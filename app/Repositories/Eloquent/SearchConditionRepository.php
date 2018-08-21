<?php


namespace App\Repositories\Eloquent;

use App\Models\SearchCondition;

class SearchConditionRepository extends BaseRepository {

    public function modelClass()
    {
        return SearchCondition::class;
    }

    /**
     * @param $advertiserId
     * @param bool $withSearchHashtags
     * @return mixed
     */
    public function getSearchConditionByAdvertiserId($advertiserId, $withSearchHashtags = false)
    {
        if ($withSearchHashtags) {

            return $this->model->where('advertiser_id', $advertiserId)->with('searchHashtags')->get();
        } else {

            return $this->model->where('advertiser_id', $advertiserId)->get();
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findWithSearchHashtag($id)
    {
        return $this->model->where('id', $id)->with('searchHashtags')->first();
    }
    
    public function getSearchConditionByHashtagIdAndAdvertiserId($hashtagId, $advertiserId) {
        $searchConditions = $this->model->join('search_hashtags', 'search_hashtags.search_condition_id', '=', 'search_conditions.id')
            ->where('search_hashtags.hashtag_id', '=', $hashtagId)
            ->where('search_conditions.advertiser_id', '=', $advertiserId)
            ->select("search_conditions.*")
            ->first();
        
        return $searchConditions;
    }
}