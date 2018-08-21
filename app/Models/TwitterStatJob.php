<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TwitterStatJob extends Model {

    protected $fillable = [
        'media_account_id',
        'job_id',
        'start_time',
        'end_time',
        'segmentation_type',
        'url',
        'entity_ids',
        'placement',
        'expires_at',
        'status',
        'granularity',
        'entity',
        'metric_groups'
    ];
}