<?php


namespace App\Service;


use App\Repositories\Eloquent\AdConversionRepository;
use App\Repositories\Eloquent\ConversionTypeRepository;

class AdConversionService extends BaseService {

    /** @var AdConversionRepository  */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(AdConversionRepository::class);
    }

    /**
     * @param $advertiserId
     * @param $conversionTypeId
     * @param $dateStart
     * @param $dateStop
     * @param $mediaType
     * @return mixed
     */
    public function getDailyConversionByAdvertiserId($advertiserId, $conversionTypeId, $dateStart, $dateStop, $mediaType = null)
    {
        /** @var ConversionTypeRepository $cvTypeRepository */
        $cvTypeRepository = app(ConversionTypeRepository::class);
        $cvType = $cvTypeRepository->find($conversionTypeId);
        $cvTypeIds = $cvTypeRepository->findAllBy('label', $cvType->label)->pluck('id')->all();

        return $this->repository->getDailyConversionByAdvertiserId($advertiserId, $cvTypeIds, $dateStart, $dateStop, $mediaType);
    }
}