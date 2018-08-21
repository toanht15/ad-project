<?php

namespace App\Providers;

use App\Service\SnsAccountService;
use Illuminate\Support\ServiceProvider;

class SnsAccountServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SnsAccountService::class, function () {
            return new SnsAccountService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SnsAccountService::class];
    }
}
