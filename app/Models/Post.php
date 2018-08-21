<?php

namespace App\Models;

use Classes\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Post
 *
 * @mixin \Eloquent
 */
class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_id',
        'post_url',
        'author_id',
        'pub_date',
        'like',
        'text',
        'comment',
        'image_url',
        'video_url',
        'file_format',
        'carousel_no'
    ];

    protected $dates = ['deleted_at'];
    // file_format
    CONST IMAGE          = 1;
    CONST VIDEO          = 2;
    CONST CAROUSEL_IMAGE = 3;
    CONST CAROUSEL_VIDEO = 4;

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public static $label = [
        self::IMAGE          => '画像',
        self::VIDEO          => '動画',
        self::CAROUSEL_IMAGE => '画像 / カルーセル投稿',
        self::CAROUSEL_VIDEO => '動画 / カルーセル投稿',
    ];

    public static function convertToVtdrMediaType($type)
    {
        switch ($type) {
            case self::IMAGE:
                return Constants::IMG_TYPE_TEXT;
            case self::VIDEO:
                return Constants::VIDEO_TYPE_TEXT;
            case self::CAROUSEL_IMAGE:   // carousel image -> vtdr image
                return Constants::IMG_TYPE_TEXT;
            case self::CAROUSEL_VIDEO:   // carousel video -> vtdr video
                return Constants::VIDEO_TYPE_TEXT;
            default:
                return null;
        }
    }
}
