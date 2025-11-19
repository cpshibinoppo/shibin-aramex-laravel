<?php

namespace Shibin\Aramex\Facades;

use Illuminate\Support\Facades\Facade;
use Shibin\Aramex\AramexClient;

class Aramex extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AramexClient::class;
    }
}
