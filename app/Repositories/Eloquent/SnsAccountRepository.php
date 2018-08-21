<?php


namespace App\Repositories\Eloquent;


use App\Models\SnsAccount;

class SnsAccountRepository extends BaseRepository {

    public function modelClass()
    {
        return SnsAccount::class;
    }
}