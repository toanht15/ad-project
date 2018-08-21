<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'origin_author_id',
        'author_id',
        'offer_id',
        'advertiser_id',
        'width',
        'height',
        'image_url',
        'video_url',
        'user_id'
    ];
}
