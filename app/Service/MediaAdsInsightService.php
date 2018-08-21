<?php


namespace App\Service;


use App\Repositories\Eloquent\MediaAdAccountInsightRepository;
use App\Repositories\Eloquent\MediaAdsInsightRepository;

class MediaAdsInsightService extends BaseService {

    /** @var  MediaAdsInsightRepository */
    protected $repository;
    /** @var  MediaAdAccountInsightRepository */
    protected $mediaAdAccountInsightRepository;

    public function __construct()
    {
        $this->repository = app(MediaAdsInsightRepository::class);
        $this->mediaAdAccountInsightRepository = app(MediaAdAccountInsightRepository::class);
    }

    /**
     * @param $advertiserId
     * @param null $dateStart
     * @param null $dateStop
     * @param null $mediaType
     * @return array
     */
    public function getAdvertiserDailyKpi($advertiserId, $dateStart = null, $dateStop = null, $mediaType = null)
    {
        $totalData = $this->repository->getAdvertiserDailyKpi($advertiserId, $dateStart, $dateStop, $mediaType);
        $dailyData = [];
        foreach ($totalData as $data) {
            $dailyData[$data->date] = $data;
        }

        return $dailyData;
    }
}