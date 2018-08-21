<?php

namespace App\Policies;


use App\Models\Advertiser;
use App\Models\Image;
use Illuminate\Auth\Access\HandlesAuthorization;


class ImagesPolicy
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

    public function update(Advertiser $advertiser, Image $image)
    {
        return $image->advertiser_id === $advertiser->id;
    }
}
