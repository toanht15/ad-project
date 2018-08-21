<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedPost extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'advertiser_id',
        'created_account_id'
    ];
}
