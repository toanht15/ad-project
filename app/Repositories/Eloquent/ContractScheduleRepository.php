<?php

namespace App\Repositories\Eloquent;

use App\Models\ContractSchedule;

class ContractScheduleRepository extends BaseRepository {
    public function modelClass()
    {
        return ContractSchedule::class;
    }
}