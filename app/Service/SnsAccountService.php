<?php


namespace App\Service;


use App\Repositories\Eloquent\SnsAccountRepository;

class SnsAccountService extends BaseService {

    /** @var SnsAccountRepository  */
    protected $repository;

    public function __construct(SnsAccountRepository $snsAccountRepository)
    {
        $this->repository = $snsAccountRepository;
    }

    /**
     * @param $mediaUserId
     * @return mixed
     */
    public function getSnsAccountByMediaUserId($mediaUserId)
    {
        return $this->repository->findBy('media_user_id', $mediaUserId);
    }
}