<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\Support\Validator;
use Shibin\Aramex\Support\SoapFactory;
use Shibin\Aramex\DTO\Pickup;
use Shibin\Aramex\Exceptions\AramexException;

class PickupModule
{
    public function __construct(
        protected AramexConfig $config
    ) {}

    public function create(Pickup $pickup)
    {
        // Validate input
        Validator::validate([
            'pickup_location' => $pickup->pickup_location,
            'pickup_date' => $pickup->pickup_date,
            'ready_time' => $pickup->ready_time,
            'last_pickup_time' => $pickup->last_pickup_time,
            'closing_time' => $pickup->closing_time,
            'weight' => $pickup->weight,
            'volume' => $pickup->volume,
            'status' => $pickup->status,
        ], [
            'pickup_location' => 'required|string',
            'pickup_date' => 'required',
            'ready_time' => 'required',
            'last_pickup_time' => 'required',
            'closing_time' => 'required',
            'weight' => 'required|numeric',
            'volume' => 'required|numeric',
            'status' => 'required|in:Ready,Pending'
        ]);

        $client = SoapFactory::client($this->config, 'shipping');

        $address = $pickup->address;

        $params = [
            'ClientInfo' => $this->config->credentials,
            'Pickup' => [
                'PickupAddress' => [
                    'Line1' => $address->line1,
                    'Line2' => $address->line2,
                    'Line3' => $address->line3,
                    'City'  => $address->city,
                    'CountryCode' => $address->country_code,
                    'PostCode' => $address->zip_code,
                ],
                'PickupContact' => [
                    'PersonName' => $address->name,
                    'CompanyName' => $address->name,
                    'PhoneNumber1' => $address->phone,
                    'CellPhone' => $address->cell_phone,
                    'EmailAddress' => $address->email,
                ],
                'PickupLocation' => $pickup->pickup_location,
                'PickupDate' => $pickup->pickup_date,
                'ReadyTime' => $pickup->ready_time,
                'LastPickupTime' => $pickup->last_pickup_time,
                'ClosingTime' => $pickup->closing_time,
                'Status' => $pickup->status,
                'PickupItems' => [
                    'PickupItemDetail' => [
                        'ProductGroup' => $this->config->defaults['ProductGroup'],
                        'ProductType' => $this->config->defaults['ProductType'],
                        'Payment' => $this->config->defaults['Payment'],
                        'NumberOfPieces' => 1,
                        'ShipmentWeight' => [
                            'Value' => $pickup->weight,
                            'Unit' => 'Kg'
                        ],
                        'ShipmentVolume' => [
                            'Value' => $pickup->volume,
                            'Unit' => 'Cm3'
                        ],
                    ],
                ],
            ],
            'LabelInfo' => $this->config->defaults['LabelInfo'],
        ];

        try {
            $response = $client->CreatePickup($params);

            if ($response->HasErrors) {
                throw new AramexException(
                    "Pickup creation failed",
                    (array)$response->Notifications
                );
            }

            return $response;
        } catch (\SoapFault $e) {
            throw new AramexException($e->getMessage());
        }
    }
}
