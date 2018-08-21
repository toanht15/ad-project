<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SnsAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'media_user_id',
        'name',
        'profile_img_url',
        'access_token',
        'refresh_token',
        'expired_at',
        'media_type'
    ];
}
