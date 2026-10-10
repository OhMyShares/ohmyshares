<?php

namespace App\Providers;

use App\Services\CurrencyService;
use App\Services\PluginService;
use App\Services\TickerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('tickerService', fn(): \App\Services\TickerService => new TickerService);

        $this->app->singleton('currencyService', fn($app): \App\Services\CurrencyService => new CurrencyService($app->make(PluginService::class)));
        $this->app->alias('currencyService', CurrencyService::class);

        $this->app->singleton(PluginService::class, fn(): \App\Services\PluginService => new PluginService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
