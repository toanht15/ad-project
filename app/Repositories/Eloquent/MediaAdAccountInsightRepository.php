<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaAdAccountInsight;

class MediaAdAccountInsightRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaAdAccountInsight::class;
    }
}