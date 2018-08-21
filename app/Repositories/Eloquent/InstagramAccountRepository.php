<?php


namespace App\Repositories\Eloquent;


use App\Models\InstagramAccount;

class InstagramAccountRepository extends BaseRepository {

    public function modelClass()
    {
        return InstagramAccount::class;
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getInstagramAccountByAdvertiserId($advertiserId)
    {
        return $this->model->join('advertiser_instagram_accounts', 'advertiser_instagram_accounts.instagram_account_id', '=', 'instagram_accounts.id')
            ->where('advertiser_instagram_accounts.advertiser_id', $advertiserId)
            ->select('instagram_accounts.*')
            ->get();
    }
    
    public function updateExpiredFlag($id, $isExpiredFlg) {
        $instagramAccount = $this->update(['expired_token_flg' => $isExpiredFlg], $id);
        
        return $instagramAccount;
    }
}