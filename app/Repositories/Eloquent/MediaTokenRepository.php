<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaToken;

class MediaTokenRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaToken::class;
    }
}