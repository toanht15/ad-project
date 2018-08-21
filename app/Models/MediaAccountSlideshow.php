<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAccountSlideshow extends Model
{
    protected $fillable = [
        'id',
        'media_account_id',
        'slideshow_id',
        'media_object_id',
        'text',
        'creative_type'
    ];
}
