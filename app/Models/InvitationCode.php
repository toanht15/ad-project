<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationCode extends Model
{
    CONST EXPIRE_DAYS = '30';

    protected $fillable = [
        'user_id',
        'invited_email',
        'created_user_id',
        'code',
        'expired_date',
        'is_used_flg'
    ];
}
