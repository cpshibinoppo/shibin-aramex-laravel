<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\Support\SoapFactory;
use Shibin\Aramex\Exceptions\AramexException;
use Illuminate\Support\Facades\Log; // Import Log

class LocationModule
{
    public function __construct(
        protected AramexConfig $config
    ) {}

    public function countries(?string $code = null)
    {
        $client = SoapFactory::client($this->config, 'location');

        $params = [
            'ClientInfo' => $this->config->credentials,
            'Transaction' => [
                'Reference1' => 'CountriesFetch',
                'Reference2' => '',
                'Reference3' => '',
                'Reference4' => '',
                'Reference5' => '',
            ],
        ];

        try {
            $response = $code
                ? $client->FetchCountry($params + ['Code' => $code])
                : $client->FetchCountries($params);

            if ($response->HasErrors) {
                $errorMsg = $response->Notifications->Notification->Message ?? "Unknown Error";
                throw new AramexException("Countries Error: " . $errorMsg, (array)$response->Notifications);
            }

            return $response;
        } catch (\SoapFault $e) {
            throw new AramexException($e->getMessage());
        }
    }

    public function cities(string $countryCode, ?string $startsWith = null)
    {
        $client = SoapFactory::client($this->config, 'location');

        // Log credentials to ensure they are correct (Debug only)
        Log::info('Aramex Auth:', (array)$this->config->credentials);

        $params = [
            'ClientInfo' => $this->config->credentials,
            
            // RE-ADDED: Required by Schema Sequence
            'Transaction' => [
                'Reference1' => 'CityList',
                'Reference2' => '',
                'Reference3' => '',
                'Reference4' => '',
                'Reference5' => '',
            ],
            
            'CountryCode' => $countryCode,
            
            // RE-ADDED: Required by Schema Sequence (Must be null if empty)
            'State' => null,
            
            'NameStartsWith' => $startsWith,
        ];

        try {
            $response = $client->FetchCities($params);

            if ($response->HasErrors) {
                // Capture the specific error message from Aramex
                $errorMsg = $response->Notifications->Notification->Message ?? "Unknown Error";
                $errorCode = $response->Notifications->Notification->Code ?? "Unknown Code";
                
                Log::error("Aramex API Failed: [$errorCode] $errorMsg");
                
                throw new AramexException(
                    "Aramex Error [$errorCode]: " . $errorMsg,
                    (array)$response->Notifications
                );
            }

            return $response;
        } catch (\SoapFault $e) {
            throw new AramexException("SOAP Fault: " . $e->getMessage());
        }
    }
}