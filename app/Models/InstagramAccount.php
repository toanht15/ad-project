<?php

namespace App\Models;

use App\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\InstagramAccount
 *
 * @mixin \Eloquent
 */
class InstagramAccount extends Model
{
    
    const EXPIRED_FLG_YES = 1;
    const EXPIRED_FLG_NO = 0;
    
    protected $fillable = [
        'name',
        'username',
        'instagram_id',
        'profile_image',
        'access_token',
        'expired_token_flg',
    ];
}
