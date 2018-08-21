<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * @param $data
     * @return Admin|Model|null|static
     */
    public static function createOrUpdate($data)
    {
        $account = self::where(['facebook_id' => $data['facebook_id']])->first();
        if (!$account) {
            $account = new Admin();
            $account->facebook_id = $data['facebook_id'];
            $account->code_id = $data['code_id'];
        }
        $account->name = $data['name'];
        $account->profile_image = $data['profile_image'];
        $account->access_token = $data['access_token'];
        $account->save();

        return $account;
    }
}
