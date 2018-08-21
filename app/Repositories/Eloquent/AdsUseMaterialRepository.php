<?php


namespace App\Repositories\Eloquent;


use App\Models\AdsUseImage;

class AdsUseMaterialRepository extends BaseRepository {

    public function modelClass()
    {
        return AdsUseImage::class;
    }
}