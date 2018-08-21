<?php


namespace App\Repositories\Eloquent;


use App\Models\AdsConversion;

class AdConversionRepository extends BaseRepository {

    public function modelClass()
    {
        return AdsConversion::class;
    }

    /**
     * @param $advertiserId
     * @param $cvTypeIds
     * @param $dateStart
     * @param $dateStop
     * @param null $mediaType
     * @return mixed
     */
    public function getDailyConversionByAdvertiserId($advertiserId, $cvTypeIds, $dateStart, $dateStop, $mediaType = null)
    {
        $query = $this->model->join('media_accounts', 'media_accounts.id', '=', 'ads_conversions.media_account_id')
            ->where('media_accounts.advertiser_id', $advertiserId);
        if ($mediaType) {
            $query = $query->where('media_type', $mediaType);
        }

        return $query->where('date', '>=', $dateStart)
            ->where('date', '<=', $dateStop)
            ->whereIn('facebook_action_id', $cvTypeIds)
            ->groupBy('date')
            ->selectRaw('sum(value) as cv, date')
            ->get();
    }
}