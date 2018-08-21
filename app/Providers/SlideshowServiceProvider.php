<?php

namespace App\Providers;

use App\Service\SlideshowService;
use Illuminate\Support\ServiceProvider;

class SlideshowServiceProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SlideshowService::class, function () {
            return new SlideshowService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SlideshowService::class];
    }
}
