<?php


namespace App\Repositories\Eloquent;


use App\Models\AdsUseSlideshow;

class AdsUseSlideshowRepository extends BaseRepository {

    public function modelClass()
    {
        return AdsUseSlideshow::class;
    }
}