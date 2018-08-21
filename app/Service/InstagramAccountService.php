<?php


namespace App\Service;


use App\Repositories\Eloquent\InstagramAccountRepository;

class InstagramAccountService extends BaseService {

    protected $repository;

    public function __construct(InstagramAccountRepository $instagramAccountRepository)
    {
        $this->repository = $instagramAccountRepository;
    }

    /**
     * @param $advertiserId
     * @param $accountId
     * @return mixed
     */
    public function getInstagramAccountByAdvertiserId($advertiserId, $accountId = null)
    {
        if(empty($accountId)) {
            return $this->repository->getInstagramAccountByAdvertiserId($advertiserId);
        } else {
            return $this->repository->findAllBy('id', $accountId);
        }
    }
}