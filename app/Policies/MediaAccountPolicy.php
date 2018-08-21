<?php

namespace App\Policies;

use App\Models\Advertiser;
use App\Models\MediaAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaAccountPolicy
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

    public function update(Advertiser $advertiser, MediaAccount $mediaAccount)
    {
        return $advertiser->id === $mediaAccount->advertiser_id;
    }
}
