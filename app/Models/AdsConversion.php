<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AdsConversion
 *
 * @mixin \Eloquent
 */
class AdsConversion extends Model
{
    protected $fillable = [
        'media_account_id',
        'facebook_ad_id',
        'facebook_ads_insight_id',
        'facebook_action_id',
        'date',
        'value'
    ];
}
