<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAdvertiser extends Model
{
    protected $fillable = [
       'advertiser_id',
        'user_id',
        'role'
    ];
}
