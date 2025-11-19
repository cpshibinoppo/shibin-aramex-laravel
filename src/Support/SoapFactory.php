<?php

namespace Shibin\Aramex\Support;

use Illuminate\Support\Facades\Log;
use SoapClient;
use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\Exceptions\AramexException;

class SoapFactory
{
    public static function client(AramexConfig $config, string $type): SoapClient
    {
        $wsdlPath = self::resolveWsdl($config, $type);

        if (!file_exists($wsdlPath)) {
            throw new AramexException("WSDL file not found: {$wsdlPath}");
        }

        $options = [
            'trace' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'exceptions' => true,
        ];

        if ($config->env === 'TEST') {
            $options['stream_context'] = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ]);
        }

        try {
            return new SoapClient($wsdlPath, $options);
        } catch (\SoapFault $e) {
            throw new AramexException("SOAP Connection Failed: " . $e->getMessage());
        }
    }

    private static function resolveWsdl(AramexConfig $config, string $type): string
    {
        $env = strtolower($config->env);

        return self::getWsdlPath() . "/{$env}/{$type}.xml";
    }

    private static function getWsdlPath(): string
    {
        // Gets the file path of AramexServiceProvider inside vendor/
        $providerFile = (new \ReflectionClass(\Shibin\Aramex\AramexServiceProvider::class))
            ->getFileName();

        // Go to package root
        return dirname($providerFile, 2) . '/wsdls';
    }
}
