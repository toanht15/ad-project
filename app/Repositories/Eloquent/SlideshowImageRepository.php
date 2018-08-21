<?php


namespace App\Repositories\Eloquent;


use App\Models\SlideshowImage;

class SlideshowImageRepository extends BaseRepository {

    public function modelClass()
    {
        return SlideshowImage::class;
    }
}