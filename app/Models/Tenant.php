<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @mixin \Eloquent
 */
class Tenant extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'created_admin_id',
        'contract_start_date',
        'contract_end_date'
    ];

    /**
     * TODO: update contract time
     * @param $data
     * @return Tenant|Model|null|static
     */
    public static function createOrUpdate($data)
    {
        $tenant = self::where(['name' => $data['name']])->first();
        if (!$tenant) {
            $tenant = new Tenant();
            $tenant->name = $data['name'];
            $tenant->created_admin_id = $data['created_admin_id'];
        }
        $tenant->save();

        return $tenant;
    }
}
