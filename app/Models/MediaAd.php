<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAd extends Model
{
    protected $fillable = [
        'media_account_id',
        'ad_id'
    ];

    const STATUS_RUNNING = 0;
    const STATUS_STOPPED = 1;

//    public function adAccount()
//    {
//        return $this->belongsTo(AdAccount::class);
//    }
}
