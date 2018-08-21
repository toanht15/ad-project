<?php


namespace App\Service;


use App\Repositories\Eloquent\AdvertiserInstagramAccountRepository;

class AdvertiserInstaAccountService extends BaseService {

    /** AdvertiserInstagramAccountRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(AdvertiserInstagramAccountRepository::class);
    }

    /**
     * @param $advertiserId
     * @param $instagramAccountId
     * @return mixed
     */
    public function findByAdvertiserIdAndIgAccountId($advertiserId, $instagramAccountId)
    {
        return $this->repository->findByAdvertiserIdAndIgAccountId($advertiserId, $instagramAccountId);
    }
}