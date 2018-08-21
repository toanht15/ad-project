<?php

namespace App\Models;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Model;

class OfferSet extends Model
{

    protected $fillable = [
        'offer_set_group_id',
        'title',
        'comment',
        'advertiser_id',
        'user_id',
        'pm_id',
        'target_count'
    ];

    const DEFAULT_ANSWER = "hiletro";

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Facebookから取得した画像のオファーセット
     *
     * @param $offerSetGroupId
     * @param $advertiserId
     * @param $createUserId
     * @return OfferSet|Model|null|static
     */
    public static function createDefaultOfferSet($offerSetGroupId, $advertiserId, $createUserId)
    {
        $defaultOfferSet = OfferSet::where('offer_set_group_id', $offerSetGroupId)->first();
        if (!$defaultOfferSet) {
            $defaultOfferSet = new OfferSet();
            $defaultOfferSet->offer_set_group_id = $offerSetGroupId;
            $defaultOfferSet->title = 'Facebook Images';
            $defaultOfferSet->advertiser_id = $advertiserId;
            $defaultOfferSet->user_id = $createUserId;
            $defaultOfferSet->save();
        }

        return $defaultOfferSet;
    }
}
