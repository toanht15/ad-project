<?php


namespace App\Service;


use App\Repositories\Eloquent\TenantRepository;

class TenantService extends BaseService {

    /** @var TenantRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(TenantRepository::class);
    }

    public function getTenantByAdvertiserId($advertiserId)
    {
        return $this->repository->getTenantByAdvertiserId($advertiserId);
    }
}