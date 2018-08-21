<?php

namespace App\Providers;

use App\Service\PartService;
use Illuminate\Support\ServiceProvider;

class PartServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
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
        $this->app->singleton(PartService::class, function ($app, $parameter) {
            $siteId = '';
            if (isset($parameter['site_id'])) {
                $siteId = $parameter['site_id'];
            } else if (\Session::get('site')) {
                $siteId = \Session::get('site')->id;
            }

            return new PartService($siteId);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [PartService::class];
    }
}
