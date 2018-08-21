<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAccount extends Model
{
    protected $fillable = [
        'media_token_id',
        'advertiser_id',
        'media_type',
        'media_account_id',
        'name'
    ];
}
