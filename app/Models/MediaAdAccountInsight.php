<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use FacebookAds\Object\Fields\AdsInsightsFields;

class MediaAdAccountInsight extends Model
{
    protected $fillable = [
        'media_account_id',
        'date',
        'spend'
    ];

    /**
     * @param null $dateStart
     * @param null $dateStop
     * @return int
     */
    public static function getTotalAdAccountSpend($dateStart = null, $dateStop = null)
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
     * @return int
     */
    public static function getTotalAdAccountSpendByDate($date)
    {
        $spend = self::where('date', $date)
            ->selectRaw('sum(spend) as sumSpend')
            ->first();
        $sumSpend = $spend->sumSpend ? $spend->sumSpend : 0;

        return  $sumSpend;
    }

    /**
     * @param $insight
     * @param $data
     * @param $adAccountId
     */
    public function createOrUpdateInsight($insight, $data, $adAccountId)
    {
        if (!$insight) {
            $insight                = new MediaAdAccountInsight();
            $insight->media_account_id = $adAccountId;
            $insight->date          = $data[AdsInsightsFields::DATE_START];
        }

        $insight->spend = $data[AdsInsightsFields::SPEND];
        $insight->save();
    }
}
