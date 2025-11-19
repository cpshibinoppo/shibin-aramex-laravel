<?php

namespace Shibin\Aramex\Config;

class AramexConfig
{
    public string $env;
    public array $credentials;
    public array $defaults;

    public function __construct(array $config)
    {
        $this->env = strtoupper($config['env'] ?? 'TEST');

        $this->credentials = $this->env === 'TEST'
            ? $config['test']
            : $config['live'];

        $this->defaults = [
            'ProductGroup' => $config['ProductGroup'],
            'ProductType' => $config['ProductType'],
            'Payment' => $config['Payment'],
            'CurrencyCode' => $config['CurrencyCode'],
            'LabelInfo' => $config['LabelInfo'],
        ];
    }
}
