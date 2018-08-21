<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Post
 *
 * @mixin \Eloquent
 */
class Slideshow extends Model
{
    use SoftDeletes;

    /** 下書き */
    const STATUS_DRAFT      = 0;
    /** 作成済み */
    const STATUS_CREATED    = 1;
    /** FBビデオライブラリーにアップロード済み */
    const STATUS_UPLOADED   = 2;
    /** 出稿済み */
    const STATUS_SPEND      = 3;

    /** 正方形ビデオ（スライドショー） */
    const VIDEO_TYPE_SQUARE     = 0;
    /** ストーリービデオ（長方形） */
    const VIDEO_TYPE_STORIES    = 1;
    
    /** フェードイン/フェードアウト **/
    const EFFECT_TYPE_FADEINOUT         = 0;
    /** 水平移動 **/
    const EFFECT_TYPE_HORIZONTAL_SLIDE  = 1;
    /** ズームイン **/
    const EFFECT_TYPE_ZOOMIN            = 2;
    /** ズームアウト **/
    const EFFECT_TYPE_ZOOMOUT           = 3;
    
    /** fps **/
    const MOVIE_FPS                     = 30;
    /** フェード時間(秒) **/
    const FADE_TIME                     = 0.5;
}
