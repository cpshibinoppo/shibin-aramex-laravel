<?php

namespace Shibin\Aramex\Services;

use GuzzleHttp\Client;

class AramexClient
{
    protected array $config;
    protected array $creds;
    protected string $shippingUrl;
    protected string $locationUrl;
    protected Client $http;

    public function __construct(array $config)
    {
        $this->config = $config;
        $env = $config['env'] ?? 'TEST';

        $this->creds = $config['credentials'][$env];
        $this->shippingUrl = $config['base_urls'][$env]['shipping'];
        $this->locationUrl = $config['base_urls'][$env]['location'];

        $sslVerify = ($env === 'LIVE');

        $this->http = new Client([
            'timeout' => 30.0,
            'verify'  => $sslVerify, // Always verify SSL on production
        ]);
    }

    /**
     * Fetch Cities (Location API)
     */
    public function fetchCities(string $countryCode)
    {
        // 1. Build the SOAP Body
        $params = [
            'ClientInfo'  => $this->getClientInfo(),
            'Transaction' => ['Reference1' => 'CityList'],
            'CountryCode' => $countryCode
        ];

        // 2. Wrap in Envelope
        $xml = $this->buildSoapEnvelope('FetchCities', $params, 'http://ws.aramex.net/ShippingAPI/v1/');

        // 3. Send POST request with correct Headers
        return $this->sendSoapRequest($this->locationUrl, 'FetchCities', $xml);
    }

    /**
     * Create Shipment (Shipping API)
     * MUST BE XML, NOT JSON
     */
    public function createShipment(array $shipmentData)
    {
        $params = [
            'ClientInfo'  => $this->getClientInfo(),
            'Transaction' => ['Reference1' => 'NewShipment'],
            'Shipments'   => [
                'Shipment' => $shipmentData // Ensure this array structure matches Aramex WSDL exactly
            ]
        ];

        $xml = $this->buildSoapEnvelope('CreateShipments', $params, 'http://ws.aramex.net/ShippingAPI/v1/');

        return $this->sendSoapRequest($this->shippingUrl, 'CreateShipments', $xml);
    }

    /**
     * Helper: Send the Request
     */
    protected function sendSoapRequest(string $url, string $action, string $xmlBody)
    {
        try {
            $response = $this->http->post($url, [
                'headers' => [
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'SOAPAction'   => "http://ws.aramex.net/ShippingAPI/v1/Service_1_0/$action"
                ],
                'body' => $xmlBody
            ]);

            return $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // --- DEBUGGING CODE ---
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();

                // Print the raw XML error from Aramex
                dd('ARAMEX 500 ERROR:', $errorBody, 'YOUR XML SENT:', $xmlBody);
            }
            // ----------------------

            return 'Error: ' . $e->getMessage();
        }
    }

    protected function getClientInfo(): array
    {
        return [
            'UserName'           => $this->creds['username'],
            'Password'           => $this->creds['password'],
            'Version'            => 'v1.0',
            'AccountNumber'      => $this->creds['account_number'],
            'AccountPin'         => $this->creds['account_pin'],
            'AccountEntity'      => $this->creds['entity'],
            'AccountCountryCode' => $this->creds['country_code'],
        ];
    }

    /**
     * Build SOAP XML
     */
    protected function buildSoapEnvelope(string $method, array $data, string $namespace): string
    {
        $innerXml = $this->arrayToXml($data);

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="{$namespace}">
   <soapenv:Header/>
   <soapenv:Body>
      <v1:{$method}>
         {$innerXml}
      </v1:{$method}>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    protected function arrayToXml(array $data): string
    {
        $xml = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // If the array has numeric keys, it's a list of identical items (like Shipments -> Shipment)
                if (array_is_list($value)) {
                    foreach ($value as $subValue) {
                        // Assuming the parent key is plural (Shipments) and child is singular (Shipment)
                        // You might need custom logic here depending on structure
                        $xml .= $this->arrayToXml($subValue);
                    }
                } else {
                    // Normal associative array
                    $xml .= "<v1:$key>" . $this->arrayToXml($value) . "</v1:$key>";
                }
            } else {
                $xml .= "<v1:$key>" . htmlspecialchars($value) . "</v1:$key>";
            }
        }
        return $xml;
    }
}
