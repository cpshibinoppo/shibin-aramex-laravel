<?php

namespace Shibin\Aramex\Services;

use Illuminate\Support\Facades\Log;
use SoapClient;

class AramexLocationService
{
    protected $client;
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $env = $config['env'] ?? 'TEST';

        // 1. Determine if we are in TEST or LIVE
        $isTest = ($env === 'TEST');
        $folder = $isTest ? 'test' : 'live';

        // 2. Calculate the path dynamically relative to THIS file
        // Assuming file is in: src/Services/AramexLocationService.php
        // We need to go up 2 levels to get to root, then into wsdls
        $wsdlPath = __DIR__ . "/../../wsdls/{$folder}/location.xml";



        // 3. Check if file exists (Optional debugging)
        if (!file_exists($wsdlPath)) {
            throw new \Exception("Aramex WSDL not found at: " . $wsdlPath);
        }

        // 4. Set SoapOptions (Disable SSL check for Test)
        $soapOptions = [
            'trace'      => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'exceptions' => true,
        ];

        if ($isTest) {
            $soapOptions['stream_context'] = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
        }

        // 5. Create Client
        $this->client = new SoapClient($wsdlPath, $soapOptions);
    }

    protected function clientInfo()
    {
        // Access the specific env credentials
        $c = $this->config['credentials'][$this->config['env']];

        return [
            'UserName'          => $c['username'],
            'Password'          => $c['password'],
            'Version'           => $c['version'],
            'AccountNumber'     => $c['account_number'],
            'AccountPin'        => $c['account_pin'],
            'AccountEntity'     => $c['entity'],
            'AccountCountryCode' => $c['country_code'],
            'Source'            => 24,
            'PreferredLanguageCode' => null,
        ];
    }

    public function fetchCities(string $countryCode,  $startsWith = null)
    {
        Log::info($this->clientInfo());
        $params = [
            'ClientInfo' => $this->clientInfo(),
            'Transaction' => ['Reference1' => 'Test'],
            'CountryCode' => $countryCode,
            'State' => null,
            'NameStartsWith' => $startsWith
        ];

        return $this->client->FetchCities($params);
    }

    public function fetchCountries()
    {
        $params = [
            'ClientInfo' => $this->clientInfo(),
            'Transaction' => ['Reference1' => 'Test']
        ];

        return $this->client->FetchCountries($params);
    }

    public function validateAddress(array $address)
    {
        $params = [
            'ClientInfo' => $this->clientInfo(),
            'Transaction' => ['Reference1' => 'Test'],
            'Address' => $address
        ];

        return $this->client->ValidateAddress($params);
    }
}
