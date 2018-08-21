<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartImagesTemporary extends Model
{
    protected $fillable = [
        'post_id',
        'post_media_id',
        'search_condition_id',
        'vtdr_image_id',
        'vtdr_site_id',
        'vtdr_part_id'
    ];
}