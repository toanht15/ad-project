<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaAdsInsight;

class MediaAdsInsightRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaAdsInsight::class;
    }

    /**
     * @param $advertiserId
     * @param null $dateStart
     * @param null $dateStop
     * @param null $mediaType
     * @return mixed
     */
    public function getAdvertiserDailyKpi($advertiserId, $dateStart = null, $dateStop = null, $mediaType = null)
    {
        $query = $this->model->join('media_accounts', 'media_accounts.id', '=', 'media_ads_insights.media_account_id')
            ->join('advertisers', 'advertisers.id', '=', 'media_accounts.advertiser_id')
            ->where('date', '>=', $dateStart)
            ->where('date', '<=', $dateStop)
            ->where('media_accounts.advertiser_id', '=', $advertiserId);

        if ($mediaType) {
            $query = $query->where('media_accounts.media_type', $mediaType);
        }

        return $query->groupBy('date')
            ->selectRaw('date, sum(click) as sum_click, sum(spend) as sum_spend, sum(impression) as sum_impression, (sum(click)*100/sum(impression)) as sum_ctr')
            ->orderBy('date')
            ->get();
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return mixed
     */
    public function getAdvertiserKpiList($beginDate, $endDate)
    {
        return $this->model->whereBetween('media_ads_insights.date', [$beginDate, $endDate])
            ->join('media_accounts', 'media_accounts.id', '=', 'media_ads_insights.media_account_id')
            ->selectRaw('advertiser_id, sum(spend) as spend')
            ->groupBy('media_accounts.advertiser_id')
            ->get();
    }

    /**
     * @param $status
     * @param $since
     * @param $until
     * @return mixed
     */
    public function getHasSpendAdIdsWithStatus($status, $since, $until)
    {
        return $this->model->join('media_ads', function($join) use ($status) {
            $join->on('media_ads.id', '=', 'media_ads_insights.facebook_ad_id');
            $join->on('media_ads.status', '=', \DB::raw($status));
        })
            ->whereBetween('media_ads_insights.date', [$since, $until])
            ->groupBy('media_ads_insights.facebook_ad_id')
            ->select('media_ads_insights.facebook_ad_id')
            ->pluck('media_ads_insights.facebook_ad_id');
    }
}