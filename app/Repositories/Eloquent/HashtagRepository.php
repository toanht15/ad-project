<?php


namespace App\Repositories\Eloquent;


use App\Models\Hashtag;

class HashtagRepository extends BaseRepository {

    public function modelClass()
    {
        return Hashtag::class;
    }

    public function getHashtagBySearchConditionId($searchConditionId)
    {
        return $this->model->join('search_hashtags', 'search_hashtags.hashtag_id', '=', 'hashtags.id')
            ->where('search_hashtags.search_condition_id', $searchConditionId)
            ->select('hashtags.*')
            ->get();
    }

    /**
     * @return mixed
     */
    public function getActiveHashtags()
    {
        return $this->model->join('search_hashtags', 'search_hashtags.hashtag_id', '=', 'hashtags.id')
            ->join('search_conditions', 'search_conditions.id', '=', 'search_hashtags.search_condition_id')
            ->selectRaw('hashtags.id, hashtags.hashtag, hashtags.last_crawled_at, hashtags.active_flg')
            ->whereIn('active_flg', [Hashtag::ACTIVE, Hashtag::CRAWLING, Hashtag::FAIL, Hashtag::WAIT])
            ->groupBy('hashtags.id')
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getHashtagAdvertiser($id)
    {
        return $this->model->join('search_hashtags', 'search_hashtags.hashtag_id', '=', 'hashtags.id')
            ->join('search_conditions', 'search_conditions.id', '=', 'search_hashtags.search_condition_id')
            ->join('advertisers', 'advertisers.id', '=', 'search_conditions.advertiser_id')
            ->selectRaw('hashtags.*, advertisers.name, advertisers.id as adv_id')
            ->where('hashtags.id', '=', $id)
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getHashtagInstagramAccount($id)
    {
        return $this->model->join('search_hashtags', 'search_hashtags.hashtag_id', '=', 'hashtags.id')
            ->join('search_conditions', 'search_conditions.id', '=', 'search_hashtags.search_condition_id')
            ->join('advertisers', 'advertisers.id', '=', 'search_conditions.advertiser_id')
            ->join('advertiser_instagram_accounts', 'advertiser_instagram_accounts.advertiser_id', '=', 'advertisers.id')
            ->join('instagram_accounts', 'instagram_accounts.id', '=', 'advertiser_instagram_accounts.instagram_account_id')
            ->selectRaw('instagram_accounts.*')
            ->where('hashtags.id', '=', $id)
            ->first();
    }

    public function getHashtag($id, $hashtag, $type = Hashtag::TYPE_HASHTAG, array $activeFlg) {
        $query = $this->model->where('type', '=', $type);

        if($id) {
            $query->where('id', '=', $id);
        }
        if($hashtag) {
            $query->where('hashtag', '=', $hashtag);
        }
        if(!empty($activeFlg)) {
            $query->whereIn('active_flg', $activeFlg);
        }

        return $query->get();
    }

    /**
     * @param $hashtagId
     * @param array $contractType
     * @return mixed
     */
    public function getHashtagAdvertiserWithContract($hashtagId, array $contractType)
    {
        return $this->model->join('search_hashtags', 'search_hashtags.hashtag_id', '=', 'hashtags.id')
            ->join('search_conditions', 'search_conditions.id', '=', 'search_hashtags.search_condition_id')
            ->join('advertisers', 'advertisers.id', '=', 'search_conditions.advertiser_id')
            ->join('contract_services', 'contract_services.advertiser_id', '=', 'advertisers.id')
            ->join('contract_schedules', 'contract_schedules.contract_service_id', '=', 'contract_services.id')
            ->selectRaw('hashtags.*, contract_schedules.*, advertisers.name, advertisers.id as adv_id')
            ->whereIn('contract_services.service_type', $contractType)
            ->where('hashtags.id', $hashtagId)
            ->get();
    }
}