<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaAd;

class MediaAdRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaAd::class;
    }

    /**
     * @param $mediaAccountId
     * @param $beginDate
     * @param $endDate
     * @return mixed
     */
    public function getStoppedAds($mediaAccountId, $beginDate, $endDate)
    {
        return $this->model->where([
            'media_account_id' => $mediaAccountId,
            'status' => MediaAd::STATUS_STOPPED
        ])->whereBetween('created_at', [$beginDate, $endDate])->get();
    }
}