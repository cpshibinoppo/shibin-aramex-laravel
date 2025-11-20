<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
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
        $client = SoapFactory::client($this->config, 'shipping');
        $address = $pickup->address;

        // TEST ENV FIX: Aramex allows pickups ONLY from Jordan (JO)
        if ($this->config->env === 'test' && $address->country_code !== 'JO') {
            throw new AramexException("In TEST mode, pickups can ONLY be created from Jordan (JO).");
        }

        // Required SOAP formatted dates
        $date = fn($d) => date('Y-m-d\TH:i:s', is_numeric($d) ? $d : strtotime($d));

        // Use user reference or auto-generate one
        $reference = $pickup->reference1 ?: 'PK-' . time();

        $params = [
            'ClientInfo' => $this->config->credentials,

            'Transaction' => [
                'Reference1' => $reference,
            ],

            'Pickup' => [
                'Reference1' => $reference,
                'PickupLocation' => $pickup->pickup_location,
                'Status' => $pickup->status,

                'PickupDate' => $date($pickup->pickup_date),
                'ReadyTime' => $date($pickup->ready_time),
                'LastPickupTime' => $date($pickup->last_pickup_time),
                'ClosingTime' => $date($pickup->closing_time),

                'PickupAddress' => [
                    'Line1' => $address->line1,
                    'Line2' => $address->line2 ?? '',
                    'Line3' => $address->line3 ?? '',
                    'City'  => $address->city,
                    'CountryCode' => $address->country_code,
                    'PostCode' => $address->zip_code ?? '',
                ],

                'PickupContact' => [
                    'PersonName' => $address->name,
                    'CompanyName' => $address->name,
                    'PhoneNumber1' => $address->phone,
                    'CellPhone' => $address->cell_phone,
                    'EmailAddress' => $address->email,
                ],

                'PickupItems' => [
                    'PickupItemDetail' => [
                        'ProductGroup' => 'EXP',    // Must be EXP for international
                        'ProductType'  => 'PPX',    // Must be PPX
                        'Payment' => 'P',           // Prepaid
                        'NumberOfPieces' => 1,
                        'NumberOfShipments' => 1,

                        'ShipmentWeight' => [
                            'Value' => $pickup->weight,
                            'Unit' => 'KG'
                        ],

                        'ShipmentVolume' => [
                            'Value' => $pickup->volume,
                            'Unit' => 'CM3'
                        ],
                    ]
                ]
            ],

            // LabelInfo is NOT ALLOWED for create pickup in TEST
            'LabelInfo' => null,
        ];

        try {
            $response = $client->CreatePickup($params);

            if ($response->HasErrors) {
                $notifications = $response->Notifications->Notification ?? [];
                $arr = is_array($notifications) ? $notifications : [$notifications];

                $errs = array_map(fn($n) => $n->Message, $arr);

                throw new AramexException(
                    "Pickup creation failed: " . implode(', ', $errs),
                    $errs
                );
            }

            return $response;

        } catch (\SoapFault $e) {
            throw new AramexException($e->getMessage());
        }
    }
}
