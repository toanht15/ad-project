<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Post
 *
 * @mixin \Eloquent
 */
class AdsUseSlideshow extends Model
{
    protected $fillable = [
        'ad_id',
        'media_slideshow_id'
    ];
}
