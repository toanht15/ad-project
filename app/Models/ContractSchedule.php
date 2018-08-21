<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractSchedule extends Model
{
    protected $fillable = [
        'contract_service_id',
        'start_date',
        'end_date'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];
}