<?php

namespace Shibin\Aramex\Wsdl;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\AramexServiceProvider;

class WsdlResolver
{
    /**
     * Returns full path to a specific WSDL file.
     *
     * @param AramexConfig $config
     * @param string $name   e.g. "location", "shipping", "rate", "tracking"
     */
    public static function path(AramexConfig $config, string $name): string
    {
        $env = strtolower($config->env);

        return self::getWsdlPath() . "/{$env}/{$name}.xml";
    }

    /**
     * Resolves the correct WSDL folder inside vendor (safe for symlink installs)
     */
    private static function getWsdlPath(): string
    {
        // Gets the actual file loaded from vendor
        $providerFile = (new \ReflectionClass(AramexServiceProvider::class))
            ->getFileName();

        // Go to package root folder
        return dirname($providerFile, 2) . '/wsdls';
    }
}
