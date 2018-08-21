<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsUseImage extends Model
{
    protected $fillable = [
        'ad_id',
        'image_entry_id'
    ];
}
