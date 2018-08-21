<?php

namespace App\Policies;

use App\Models\Advertiser;
use App\Models\Slideshow;
use Illuminate\Auth\Access\HandlesAuthorization;

class SlideshowPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function update(Advertiser $advertiser, Slideshow $slideshow)
    {
        return $slideshow->advertiser_id === $advertiser->id;
    }
}
