<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\Support\SoapFactory;
use Shibin\Aramex\Support\Validator;
use Shibin\Aramex\Exceptions\AramexException;

class TrackingModule
{
    public function __construct(
        protected AramexConfig $config
    ) {}

    public function track(array $shipments, bool $lastUpdateOnly = false)
    {
        Validator::validate([
            'shipments' => $shipments
        ], [
            'shipments' => 'required|array',
        ]);

        $client = SoapFactory::client($this->config, 'tracking');

        $clientInfo = $this->config->credentials;
        if (array_key_exists('Source', $clientInfo) && is_null($clientInfo['Source'])) {
            unset($clientInfo['Source']);
        }

        $params = [
            'ClientInfo' => $clientInfo,

            'Transaction' => [
                'Reference1' => 'TrackShipment',
                'Reference2' => '',
                'Reference3' => '',
                'Reference4' => '',
                'Reference5' => '',
            ],

            'Shipments' => [
                'string' => $shipments
            ],
            
            'GetLastTrackingUpdateOnly' => $lastUpdateOnly
        ];

        try {
            $response = $client->TrackShipments($params);

            if ($response->HasErrors) {
                $errorMsg = $response->Notifications->Notification->Message ?? "Unknown Error";
                throw new AramexException(
                    "Tracking failed: " . $errorMsg,
                    (array)$response->Notifications
                );
            }

            return $response;
        } catch (\SoapFault $e) {
            throw new AramexException($e->getMessage());
        }
    }
}