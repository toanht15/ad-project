<?php

namespace App\Models;

use Classes\Roles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    const IS_EGC_STAFF_TRUE = 1;
    const IS_EGC_STAFF_FALSE = 0;

    protected $fillable = [
        'email',
        'user_name',
        'tenant_id',
        'profile_img_url',
        'role',
        'is_egc_staff',
    ];

    // dont use remember token
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {

    }

    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Overrides the method to ignore the remember token.
     */
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        {
            parent::setAttribute($key, $value);
        }
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role == Roles::ADMIN;
    }

    public function isSuperAdmin()
    {
        return $this->isAdmin() && !$this->isEgcStaff();
    }

    /**
     * @return bool
     */
    public function isEgcStaff()
    {
        return $this->is_egc_staff == self::IS_EGC_STAFF_TRUE;
    }

    /**
     * @param $mediaType
     * @return mixed
     */
    public function getAccessToken($mediaType) {
        $snsAccount = SnsAccount::where(['user_id' => $this->id, 'media_type' => $mediaType])->first();

        return $snsAccount->access_token;
    }

}
