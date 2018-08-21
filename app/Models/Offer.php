<?php

namespace App\Models;

use App\Repositories\Eloquent\OfferRepository;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'offer_set_id',
        'advertiser_id',
        'post_id',
        'user_id',
        'comment_temp_id',
        'status'
    ];

    /** リクエスト作成、まだコメントしていない */
    const STATUS_OFFERED            = 0;
    /** コメントした */
    const STATUS_COMMENTED          = 1;
    /** コメントに失敗しました */
    const STATUS_COMMENT_FALSE      = 2;
    /** 承認済み */
    const STATUS_APPROVED           = 3;
    /** 出稿済 */
    const STATUS_LIVING             = 4;
    /** 非表示 */
    const STATUS_ARCHIVE            = 5;
    /** UGCセット連携済み */
    const STATUS_REGISTERED_PART    = 6;
    
    public static $offerStatusLabels = array(
        self::STATUS_OFFERED         => 'リクエスト作成',
        self::STATUS_COMMENTED       => 'コメントした',
        self::STATUS_COMMENT_FALSE   => 'コメントに失敗しました',
        self::STATUS_APPROVED        => '承認済み',
        self::STATUS_LIVING          => '出稿済',
        self::STATUS_ARCHIVE         => '非表示',
        self::STATUS_REGISTERED_PART => 'UGCセット連携済み'
    );

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function offerSet()
    {
        return $this->belongsTo(OfferSet::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @param $advertiserId
     * @param $ids
     * @param $actionId
     * @param $dateStart
     * @param $dateStop
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDailyCVReportByIds($advertiserId, $ids, $actionId, $dateStart, $dateStop)
    {
        $result = self::whereIn('offers.id', $ids)
            ->join('images', 'images.offer_id', '=', 'offers.id')
            ->join('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->join('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->join('ads_conversions', function ($join) use ($advertiserId, $actionId) {
                $join->on('ads_conversions.facebook_ad_id', '=', 'ads_use_images.ad_id');
                $join->on(\DB::raw('ads_conversions.media_account_id = '.$advertiserId), \DB::raw(''), \DB::raw(''));
                $join->on(\DB::raw('ads_conversions.facebook_action_id = '.$actionId), \DB::raw(''), \DB::raw(''));
            })
            ->where('ads_conversions.date', '>=', $dateStart)
            ->where('ads_conversions.date', '<=', $dateStop)
            ->groupBy(['offers.id', 'date', 'facebook_action_id'])
            ->orderBy('date')
            ->selectRaw('offers.id, date, facebook_action_id, sum(value) as value')
            ->get();

        return $result;
    }

    /**
     * TODO delete this function
     *
     * @return mixed
     */
    public static function getOfferReport()
    {
        /** @var OfferRepository $repository */
        $repository = app(OfferRepository::class);
        return $repository->getOfferWithReportBaseQuery();
    }

    /**
     * @param $status
     * @return string
     */
    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_APPROVED:
                return '<span class="label label-approved p5 status">承認</span>';
            case self::STATUS_COMMENTED:
                return '<span class="label label-applying p5 status">申請中</span>';
            case self::STATUS_OFFERED:
                return '<span class="label label-applying p5 status">申請中</span>';
            case self::STATUS_COMMENT_FALSE:
                return '<span class="label label-failed p5 status">失敗</span>';
            case self::STATUS_LIVING:
                return '<span class="label label-synchronis p5 status">出稿済</span>';
            case self::STATUS_ARCHIVE:
                return '<span class="label label-uploaded p5 status">取り消し</span>';
            default:
                return "";
        }
    }

    /**
     * @return mixed
     */
    public static function countApprovalOffer()
    {
        return self::whereIn('status', [self::STATUS_APPROVED, self::STATUS_LIVING])->count();
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @param array $status
     * @return mixed
     */
    public static function countInPeriod($beginDate, $endDate, $status = [])
    {
        $query = Offer::whereBetween('created_at', [$beginDate, $endDate]);
        if (count($status)) {
            $query = $query->whereIn('status', $status);
        }

        return $query->count();
    }
}
