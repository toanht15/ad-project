<?php


namespace App\Service;


use App\Repositories\Eloquent\AdsUseMaterialRepository;

class AdsUseMaterialService extends BaseService {

    protected $repository;

    public function __construct()
    {
        $this->repository = app(AdsUseMaterialRepository::class);
    }
}