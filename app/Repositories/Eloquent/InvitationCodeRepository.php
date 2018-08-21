<?php


namespace App\Repositories\Eloquent;


use App\Models\InvitationCode;

class InvitationCodeRepository extends BaseRepository {

    public function modelClass()
    {
        return InvitationCode::class;
    }
}