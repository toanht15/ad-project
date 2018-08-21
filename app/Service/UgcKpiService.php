<?php
namespace App\Service;

use App\Repositories\Eloquent\MediaAdsInsightRepository;

class UgcKpiService extends BaseService
{
    public static $instant;

    /** @var MediaAdsInsightRepository  */
    protected $mediaAdsInsightRepository;

    public function __construct()
    {
        $this->mediaAdsInsightRepository = app(MediaAdsInsightRepository::class);
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return array
     */
    public function getUgcSpendGroupByAdAccount($beginDate, $endDate)
    {
        $result = [];
        $spendData = $this->mediaAdsInsightRepository->getAdvertiserKpiList($beginDate, $endDate);

        foreach ($spendData as $data) {
            $result[$data->advertiser_id] = $data->spend;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public static function getInstant()
    {
        if (!static::$instant) {
            static::$instant = new UgcKpiService();
        }
        return static::$instant;
    }
}