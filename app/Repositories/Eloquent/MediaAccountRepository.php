<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaAccount;

class MediaAccountRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaAccount::class;
    }

    /**
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function findWithToken($attribute, $value)
    {
        return $this->model->join('media_tokens', 'media_tokens.id', '=', 'media_accounts.media_token_id')
            ->where('media_accounts.'.$attribute, $value)
            ->selectRaw('media_accounts.*, media_tokens.access_token, media_tokens.refresh_token, media_tokens.media_account_id as media_user_id, media_tokens.token_expired_flg')
            ->get();
    }

    /**
     * @param null $advertiserId
     * @return mixed
     */
    public function getMediaAccountsWithToken($advertiserId = null)
    {
        if ($advertiserId) {
            return $this->model->join('media_tokens', 'media_tokens.id', '=', 'media_accounts.media_token_id')
                ->where('media_accounts.advertiser_id', $advertiserId)
                ->selectRaw('media_accounts.*, media_tokens.access_token, media_tokens.token_expired_flg, media_tokens.media_account_id as media_user_id')
                ->get();
        }
        return $this->model->join('media_tokens', 'media_tokens.id', '=', 'media_accounts.media_token_id')
            ->selectRaw('media_accounts.*, media_tokens.access_token, media_tokens.token_expired_flg, media_tokens.refresh_token, media_tokens.media_account_id as media_user_id')
            ->get();
    }
}