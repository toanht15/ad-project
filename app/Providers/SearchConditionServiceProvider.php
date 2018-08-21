<?php

namespace App\Providers;

use App\Repositories\Eloquent\SearchConditionRepository;
use App\Service\SearchConditionService;
use Illuminate\Support\ServiceProvider;

class SearchConditionServiceProvider extends ServiceProvider
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
        $this->app->singleton(SearchConditionService::class, function () {
            return new SearchConditionService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SearchConditionService::class];
    }
}
