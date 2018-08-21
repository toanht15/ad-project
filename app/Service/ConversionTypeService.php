<?php


namespace App\Service;


use App\Repositories\Eloquent\ConversionTypeRepository;

class ConversionTypeService extends BaseService {

    /** @var ConversionTypeRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(ConversionTypeRepository::class);
    }

    /**
     * @param $mediaAccountId
     * @return mixed
     */
    public function getUnlabelCustomConversions($mediaAccountId)
    {
        return $this->repository->getUnlabelCustomConversions($mediaAccountId);
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getAvailableConversionType($advertiserId)
    {
        return $this->repository->getAvailableConversionType($advertiserId);
    }
}