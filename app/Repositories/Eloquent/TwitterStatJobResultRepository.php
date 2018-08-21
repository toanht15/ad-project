<?php


namespace App\Repositories\Eloquent;


use App\Models\TwitterStatJobResult;

class TwitterStatJobResultRepository extends BaseRepository {

    public function modelClass()
    {
        return TwitterStatJobResult::class;
    }
}