<?php

namespace Shibin\Aramex;

use Shibin\Aramex\Config\AramexConfig;
use Shibin\Aramex\Modules\PickupModule;
use Shibin\Aramex\Modules\ShipmentModule;
use Shibin\Aramex\Modules\TrackingModule;
use Shibin\Aramex\Modules\RateModule;
use Shibin\Aramex\Modules\LocationModule;

class AramexClient
{
    protected AramexConfig $config;

    public function __construct(array $config)
    {
        $this->config = new AramexConfig($config);
    }

    public function pickup(): PickupModule
    {
        return new PickupModule($this->config);
    }

    public function shipments(): ShipmentModule
    {
        return new ShipmentModule($this->config);
    }

    public function tracking(): TrackingModule
    {
        return new TrackingModule($this->config);
    }

    public function rates(): RateModule
    {
        return new RateModule($this->config);
    }

    public function location(): LocationModule
    {
        return new LocationModule($this->config);
    }
}
