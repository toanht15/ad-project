<?php


namespace App\Service;


use App\Repositories\Eloquent\OfferSetGroupRepository;

class OfferSetGroupService extends BaseService {

    /** @var OfferSetGroupRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(OfferSetGroupRepository::class);
    }

    /**
     * @param $advertiserId
     * @param $title
     * @return mixed
     */
    public function create($advertiserId, $title)
    {
        return $this->repository->create([
            'advertiser_id' => $advertiserId,
            'title' => $title
        ]);
    }
}