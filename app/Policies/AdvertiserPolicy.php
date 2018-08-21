<?php

namespace App\Policies;

use App\Models\Advertiser;
use App\Models\User;
use App\Models\UserAdvertiser;
use Classes\Roles;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertiserPolicy
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

    /**
     * @param User $user
     * @param Advertiser $advertiser
     * @return bool
     */
    public function addMediaAccount(User $user, Advertiser $advertiser)
    {
        $acceptRoles = [
            Roles::ADMIN,
            Roles::AGENT,
            Roles::ADVERTISER,
            Roles::AA_USER
        ];

        return $this->checkRoles($user, $advertiser, $acceptRoles);
    }

    /**
     * @param User $user
     * @param Advertiser $advertiser
     * @return bool
     */
    public function view(User $user, Advertiser $advertiser)
    {
        $acceptRoles = [
            Roles::ADMIN,
            Roles::AGENT,
            Roles::ADVERTISER,
            Roles::AA_USER,
            Roles::USER
        ];

        return $this->checkRoles($user, $advertiser, $acceptRoles);
    }

    /**
     * @param User $user
     * @param Advertiser $advertiser
     * @param array $roles
     * @return bool
     */
    private function checkRoles(User $user, Advertiser $advertiser, $roles = [])
    {
        $userAdvertiser = UserAdvertiser::where([
            'user_id' => $user->id,
            'advertiser_id' => $advertiser->id
        ])->first();

        if (in_array($userAdvertiser->role, $roles)) {
            return true;
        }

        return false;
    }
}
