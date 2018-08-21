<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Advertiser extends Authenticatable
{
    const SEARCH_CONDITION_LIMIT = 100;
    const SETTING_VALUE_NO = 0;
    const SETTING_VALUE_YES = 1;

    protected $fillable = [
        'name',
        'tenant_id'
    ];

    use SoftDeletes;

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
     * Overrides the method to ignore the remember token.
     */
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute) {
            parent::setAttribute($key, $value);
        }
    }

    public function contractServices()
    {
        return $this->hasMany(ContractService::class);
    }

    public function isFirstOwnerLogin()
    {

        $site = \Session::get('site');
        if (!$site)
            return false;
        $ownerContract = $this->contractServices()->where('vtdr_site_id', $site->id)->first();
        if (!$ownerContract) {
            return false;
        } else
            return $ownerContract->is_owned_first;
    }


    public function setIsOwnedFirst()
    {
        $site = \Session::get('site');
        if (!$site)
            return;
        $ownerContract = $this->contractServices()->where('vtdr_site_id', $site->id)->first();
        $ownerContract->is_owned_first = false;
        $ownerContract->save();
    }
    
    public function countOfferByStatus($status) {
        $offerCount = Offer::where('advertiser_id', '=', $this->id)
            ->where('status', '=', $status)
            ->count();
    
        return $offerCount;
    }
}
