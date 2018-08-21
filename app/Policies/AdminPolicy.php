<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
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
     * @param User $admin
     * @return bool
     */
    public function hasFullPermission(User $user, User $admin)
    {
        return $admin->isSuperAdmin();
    }
}
