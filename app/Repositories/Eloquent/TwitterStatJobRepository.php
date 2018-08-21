<?php


namespace App\Repositories\Eloquent;


use App\Models\TwitterStatJob;

class TwitterStatJobRepository extends BaseRepository {

    public function modelClass()
    {
        return TwitterStatJob::class;
    }
}