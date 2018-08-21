<?php


namespace App\Repositories\Eloquent;


use App\Models\Tenant;

class TenantRepository extends BaseRepository {

    public function modelClass()
    {
        return Tenant::class;
    }

    public function getTenantByAdvertiserId($advertiserId)
    {
        return $this->model->join('advertisers', 'advertisers.tenant_id', '=', 'tenants.id')
            ->where('advertisers.id', $advertiserId)
            ->select('tenants.*')
            ->first();
    }
}