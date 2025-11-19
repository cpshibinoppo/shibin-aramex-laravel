<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\DTO\Shipment;
use Shibin\Aramex\Support\Validator;
use Shibin\Aramex\Support\SoapFactory;
use Shibin\Aramex\Exceptions\AramexException;

class ShipmentModule
{
    public function __construct(
        protected AramexConfig $config
    ) {}

    public function create(Shipment $shipment)
    {
        Validator::validate([
            'shipping_date_time' => $shipment->shipping_date_time,
            'due_date' => $shipment->due_date,
            'pickup_location' => $shipment->pickup_location,
            'weight' => $shipment->weight,
        ], [
            'shipping_date_time' => 'required',
            'due_date' => 'required',
            'pickup_location' => 'required|string',
            'weight' => 'required|numeric'
        ]);

        $client = SoapFactory::client($this->config, 'shipping');

        $clientInfo = $this->config->credentials;
        if (array_key_exists('Source', $clientInfo) && is_null($clientInfo['Source'])) {
            unset($clientInfo['Source']);
        }

        $params = [
            'ClientInfo' => $clientInfo,

            'Transaction' => [
                'Reference1' => 'Shipment',
                'Reference2' => '',
                'Reference3' => '',
                'Reference4' => '',
                'Reference5' => '',
            ],

            'Shipments' => [
                'Shipment' => [
                    'Shipper' => [
                        'Reference1' => '',
                        'Reference2' => '',
                        'AccountNumber' => $this->config->credentials['AccountNumber'],
                        'PartyAddress' => $this->mapAddress($shipment->shipper),
                        'Contact' => $this->mapContact($shipment->shipper),
                    ],
                    'Consignee' => [
                        'Reference1' => '',
                        'Reference2' => '',
                        'AccountNumber' => '',
                        'PartyAddress' => $this->mapAddress($shipment->consignee),
                        'Contact' => $this->mapContact($shipment->consignee),
                    ],
                    'ThirdParty' => null,
                    'Reference1' => '',
                    'Reference2' => '',
                    'Reference3' => '',
                    'ShippingDateTime' => $shipment->shipping_date_time,
                    'DueDate' => $shipment->due_date,
                    'Comments' => $shipment->comments ?? '',
                    'PickupLocation' => $shipment->pickup_location,
                    'OperationsInstructions' => '',
                    'AccountingInstrcutions' => '',
                    'Details' => [
                        'Dimensions' => null,
                        'ActualWeight' => ['Value' => $shipment->weight, 'Unit' => 'KG'],
                        'ChargeableWeight' => null,
                        'DescriptionOfGoods' => $shipment->description ?? 'General Goods',
                        'GoodsOriginCountry' => $shipment->shipper->country_code,
                        'NumberOfPieces' => $shipment->number_of_pieces,
                        'ProductGroup' => $this->config->defaults['ProductGroup'],
                        'ProductType' => $this->config->defaults['ProductType'],
                        'PaymentType' => $shipment->payment_type ?? $this->config->defaults['Payment'],
                        'PaymentOptions' => '',
                        'CustomsValueAmount' => null,
                        'CashOnDeliveryAmount' => null,
                        'InsuranceAmount' => null,
                        'CashAdditionalAmount' => null,
                        'CashAdditionalAmountDescription' => '',
                        'CollectAmount' => null,
                        'Services' => '',
                        'Items' => null,
                    ],
                ],
            ],
            'LabelInfo' => $this->config->defaults['LabelInfo'],
        ];

        try {
            $response = $client->CreateShipments($params);

            if ($response->HasErrors) {
                $errorMsg = "Unknown Error";
                $notifications = $response->Shipments->ProcessedShipment->Notifications->Notification ?? null;

                if ($notifications) {
                    $errorMsg = is_array($notifications) ? $notifications[0]->Message : $notifications->Message;
                } else {

                    $errorMsg = $response->Notifications->Notification->Message ?? $errorMsg;
                }

                throw new AramexException("Shipment Failed: " . $errorMsg, (array)$notifications);
            }

            return $response;
        } catch (\SoapFault $e) {
            throw new AramexException($e->getMessage());
        }
    }

    private function mapAddress($a)
    {
        return [
            'Line1' => $a->line1,
            'Line2' => $a->line2 ?? '',
            'Line3' => $a->line3 ?? '',
            'City' => $a->city,
            'StateOrProvinceCode' => null,
            'PostCode' => $a->zip_code ?? '',
            'CountryCode' => $a->country_code,
        ];
    }

    private function mapContact($c)
    {
        return [
            'Department' => '',
            'PersonName' => $c->name ?? 'N/A',
            'Title' => '',
            'CompanyName' => $c->name ?? 'N/A',
            'PhoneNumber1' => $c->phone ?? '',
            'PhoneNumber1Ext' => '',
            'PhoneNumber2' => '',
            'PhoneNumber2Ext' => '',
            'FaxNumber' => '',
            'CellPhone' => $c->cell_phone ?? $c->phone ?? '',
            'EmailAddress' => $c->email ?? '',
            'Type' => '',
        ];
    }

    private function makeMoney($value, $currency)
    {
        if (!$value || $value <= 0) return null;
        return ['Value' => $value, 'CurrencyCode' => $currency];
    }
}
