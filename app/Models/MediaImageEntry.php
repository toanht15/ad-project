<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MediaImageEntry
 *
 * @mixin \Eloquent
 */
class MediaImageEntry extends Model
{
    protected $fillable = [
        'image_id',
        'media_account_id',
        'status',
        'img_url',
        'hash_code',
        'creative_type',
        'text'
    ];
}
