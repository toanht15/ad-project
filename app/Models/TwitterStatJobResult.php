<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TwitterStatJobResult extends Model {

    protected $fillable = [
        'job_id',
        'result'
    ];
}