<?php

namespace App\Models;

use App\Service\SearchConditionService;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;

class SearchCondition extends Model
{
    /**
     * 1アカウントに対する登録上限数
     */
    const MAX_COUNT = 10;

    /** score_status */
    /** 計算中 */
    const STATUS_SCORING        = 0;
    /** 詳細スコア計算済み */
    const STATUS_DETAIL_SCORED  = 1;
    /** 計算完了 */
    const STATUS_SCORED         = 2;
    /** Adminレビュー済み */
    const STATUS_REVIEWED       = 4;
    /** User通知済み */
    const USER_NOTIFIED         = 5;

    /** inspiration_status */
    /** インスピレーション未実行 */
    const INSPIRATION_INCOMPLETE    = 0;
    /** インスピレーション完了 */
    const INSPIRATION_COMPLETE      = 1;
    /** インスピレーションスコア計算済み */
    const INSPIRATION_SCORED        = 2;

    protected $fillable = [
        'post_count',
        'title',
        'advertiser_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function searchHashtags()
    {
        return $this->hasMany(SearchHashtag::class);
    }
}
