<?php


namespace App\Repositories\Eloquent;


use App\Models\UserAdvertiser;

class UserAdvertiserRepository extends BaseRepository {

    public function modelClass()
    {
        return UserAdvertiser::class;
    }
}