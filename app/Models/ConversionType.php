<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ConversionType
 *
 * @mixin \Eloquent
 */
class ConversionType extends Model
{
    protected $fillable = [
        'action_type',
        'label'
    ];
}
