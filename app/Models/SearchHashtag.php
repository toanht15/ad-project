<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchHashtag extends Model
{
    protected $fillable = [
        'search_condition_id',
        'hashtag_id'
    ];

    /**
     * @param $searchConditionId
     * @param $hashtagId
     * @return SearchHashtag
     */
    public static function createOrUpdate($searchConditionId, $hashtagId)
    {
        $searchHashtag = SearchHashtag::where([
            'search_condition_id' => $searchConditionId,
            'hashtag_id' => $hashtagId
        ])->first();

        if (!$searchHashtag) {
            $searchHashtag = new SearchHashtag();
            $searchHashtag->search_condition_id = $searchConditionId;
            $searchHashtag->hashtag_id = $hashtagId;
            $searchHashtag->save();
        }

        return $searchHashtag;
    }
}
