<?php

namespace App\Providers;

use App\Http\ViewComposers\MasterComposer;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * return void
     */
    public function boot()
    {
        view()->composer('layouts/master', MasterComposer::class);
    }

    /**
     * Register
     */
    public function register()
    {
    }
}
