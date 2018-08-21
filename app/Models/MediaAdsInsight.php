<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAdsInsight extends Model
{
    protected $fillable = [
        'media_account_id',
        'facebook_ad_id',
        'date',
        'click',
        'spend',
        'impression',
        'ctr'
    ];

    /**
     * @param null $dateStart
     * @param null $dateStop
     * @return array
     */
    public static function getDailyTotalInsightData($dateStart = null, $dateStop = null)
    {
        $insightData = self::where('date', '>=', $dateStart)
            ->where('date', '<=', $dateStop)
            ->groupBy('date')
            ->selectRaw('date, sum(click) as sum_click, sum(spend) as sum_spend, sum(impression) as sum_impression, (sum(click)/sum(impression)) as sum_ctr')
            ->orderBy('date')
            ->get();

        return $insightData->toArray();
    }

    /**
     * @param null $dateStart
     * @param null $dateStop
     * @return float|int
     */
    public static function getTotalSpend($dateStart = null, $dateStop = null)
    {
        if ($dateStart && $dateStop) {
            $totalSpend = self::whereBetween('date', [$dateStart, $dateStop])->sum('spend');
        } else {
            $totalSpend = self::sum('spend');
        }

        $sumSpend = $totalSpend ? $totalSpend : 0;

        return $sumSpend;
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function getTotalCtrByDate($date)
    {
        $ctrData = self::where('media_ads_insights.date', $date)
            ->join('media_accounts', 'media_accounts.id', '=', 'media_ads_insights.media_account_id')
            ->join('advertisers', 'advertisers.id', '=', 'media_accounts.advertiser_id')
            ->groupBy('advertisers.id')
            ->selectRaw('advertisers.name as name, advertisers.id as id, sum(media_ads_insights.click)/sum(media_ads_insights.impression) as sum_ctr')
            ->orderBy('sum_ctr')
            ->get();

        return $ctrData->toArray();
    }

    /**
     * @param $date
     * @return int
     */
    public static function getSumSpendByDate($date)
    {
        $spend = self::where('media_ads_insights.date', $date)
            ->selectRaw('sum(spend) as sumSpend')
            ->first();
        $sumSpend = $spend->sumSpend ? $spend->sumSpend : 0;

        return  $sumSpend;
    }
}
