<?php

namespace Shibin\Aramex;

use Illuminate\Support\ServiceProvider;
use Shibin\Aramex\AramexClient;

class AramexServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/aramex.php', 'aramex');

        $this->app->singleton(AramexClient::class, function ($app) {
            return new AramexClient(\config('aramex'));
        });

        // alias for easier access
        $this->app->alias(AramexClient::class, 'aramex');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/aramex.php' => config_path('aramex.php'),
        ], 'aramex-config');
    }
}
