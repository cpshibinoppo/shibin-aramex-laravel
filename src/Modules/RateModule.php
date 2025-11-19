<?php

namespace Shibin\Aramex\Modules;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\DTO\RateRequest;
use Shibin\Aramex\Support\Validator;
use Shibin\Aramex\Support\SoapFactory;
use Shibin\Aramex\Exceptions\AramexException;

class RateModule
{
    public function __construct(
        protected AramexConfig $config
    ) {}

    public function calculate(RateRequest $rate)
    {
        Validator::validate([
            'weight' => $rate->weight,
            'pieces' => $rate->pieces
        ], [
            'weight' => 'required|numeric',
            'pieces' => 'required|numeric'
        ]);

        $client = SoapFactory::client($this->config, 'rate');

        $params = [
            'ClientInfo' => $this->config->credentials,
            'Transaction' => [
                'Reference1' => 'RateRequest',
                'Reference2' => '',
                'Reference3' => '',
                'Reference4' => '',
                'Reference5' => '',
            ],
            'OriginAddress' => $this->mapAddress($rate->origin),
            'DestinationAddress' => $this->mapAddress($rate->destination),
            'ShipmentDetails' => [
                'PaymentType' => $this->config->defaults['Payment'],
                'ProductGroup' => $this->config->defaults['ProductGroup'],
                'ProductType' => $this->config->defaults['ProductType'],
                'ActualWeight' => [
                    'Value' => $rate->weight,
                    'Unit' => 'KG'
                ],
                'NumberOfPieces' => $rate->pieces,
                'Dimensions' => null,
                'ChargeableWeight' => null,
                'DescriptionOfGoods' => 'General Goods',
                'GoodsOriginCountry' => $rate->origin->country_code,
                'Services' => '', // Send empty string, not null
            ],
            'PreferredCurrencyCode' => $rate->currency,
            'ShipmentDetails' => [
                'PaymentType' => $this->config->defaults['Payment'],
                'ProductGroup' => $this->config->defaults['ProductGroup'],
                'ProductType' => $this->config->defaults['ProductType'],
                'ActualWeight' => [
                    'Value' => $rate->weight,
                    'Unit' => 'KG'
                ],
                'NumberOfPieces' => $rate->pieces,
            ]
        ];

        if ($rate->length && $rate->width && $rate->height) {
            $params['ShipmentDetails']['Dimensions'] = [
                'Length' => $rate->length,
                'Width' => $rate->width,
                'Height' => $rate->height,
                'Unit' => 'CM'
            ];
        }

        try {
            $response = $client->CalculateRate($params);

            if ($response->HasErrors) {
                throw new AramexException(
                    "Rate calculation failed",
                    (array)$response->Notifications
                );
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
}
