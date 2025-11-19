<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\DTO\TrackingRequest;
use Shibin\Aramex\Support\SoapFactory;
use Shibin\Aramex\Support\Validator;
use Shibin\Aramex\Exceptions\AramexException;

class TrackingModule
{
    public function __construct(
        protected AramexConfig $config
    ) {}

    public function track(array $shipments)
    {
        Validator::validate([
            'shipments' => $shipments
        ], [
            'shipments' => 'required|array',
        ]);

        $client = SoapFactory::client($this->config, 'tracking');

        $params = [
            'ClientInfo' => $this->config->credentials,
            'Shipments' => $shipments
        ];

        try {
            $response = $client->TrackShipments($params);

            if ($response->HasErrors) {
                throw new AramexException(
                    "Tracking failed",
                    (array)$response->Notifications
                );
            }

            return $response;
        } catch (\SoapFault $e) {
            throw new AramexException($e->getMessage());
        }
    }
}
