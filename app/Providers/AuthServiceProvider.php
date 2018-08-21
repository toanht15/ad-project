<?php

namespace App\Providers;

use App\Models\Advertiser;
use App\Models\Image;
use App\Models\MediaAccount;
use App\Models\Slideshow;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\AdvertiserPolicy;
use App\Policies\ImagesPolicy;
use App\Policies\MediaAccountPolicy;
use App\Policies\SlideshowPolicy;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Image::class => ImagesPolicy::class,
        Slideshow::class => SlideshowPolicy::class,
        Advertiser::class => AdvertiserPolicy::class,
        User::class => AdminPolicy::class,
        MediaAccount::class => MediaAccountPolicy::class
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
    }
}
