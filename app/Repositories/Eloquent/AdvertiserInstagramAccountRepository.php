<?php


namespace App\Repositories\Eloquent;


use App\Models\AdvertiserInstagramAccount;

class AdvertiserInstagramAccountRepository extends BaseRepository {

    public function modelClass()
    {
        return AdvertiserInstagramAccount::class;
    }

    /**
     * @param $advertiserId
     * @param $instagramAccountId
     * @return mixed
     */
    public function findByAdvertiserIdAndIgAccountId($advertiserId, $instagramAccountId)
    {
        return $this->model->where([
            'advertiser_id' => $advertiserId,
            'instagram_account_id' => $instagramAccountId
        ])->first();
    }
}