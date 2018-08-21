<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaToken extends Model
{
    protected $fillable = [
        'media_account_id',
        'media_type',
        'access_token',
        'refresh_token',
        'token_expired_flg'
    ];
}
