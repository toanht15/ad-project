<?php


namespace App\Service;

use App\Repositories\Eloquent\PartRequestRepository;

class PartRequestService extends BaseService
{

    /** @var PartRequestRepository $partRequestRepository */
    protected $repository;

    public function __construct(PartRequestRepository $partRequestRepository)
    {
        $this->repository = $partRequestRepository;
    }
    
    /**
     * @param $filter
     * @return mixed
     */
    public function getPartRequest($filter)
    {
        return $this->repository->getPartRequest($filter);
    }
}