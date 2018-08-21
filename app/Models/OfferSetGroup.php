<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Post
 *
 * @mixin \Eloquent
 */
class OfferSetGroup extends Model
{

    protected $fillable = [
        'advertiser_id',
        'title'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function offerSets()
    {
        return $this->hasMany(OfferSet::class);
    }

    /**
     * @param $offerSetGroupId
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getAllOffers($offerSetGroupId)
    {
        return Offer::getOfferReport()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId)
            ->get();
    }

    /**
     * @param $offerSetGroupId
     * @param $column
     * @param $operation
     * @param $value
     * @param bool $isHaving
     * @param string $order
     * @param string $direction
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOfferWithFilter($offerSetGroupId, $column, $operation, $value, $isHaving = false, $order = 'created_at', $direction = 'desc')
    {
        $response = Offer::getOfferReport()
            ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId);

        if ($isHaving) {
            $response = $response->having($column, $operation, $value);
        } else {
            $response = $response->where($column, $operation, $value);
        }

        $response->orderBy($order, $direction);

        return $response->get();
    }

    /**
     * @param $advertiserId
     * @return OfferSetGroup|Model|null|static
     */
    public static function createDefaultOfferSetGroup($advertiserId)
    {
        $offerSetGroupTitle = 'Facebook images';
        $offerSetGroup = OfferSetGroup::where([
            'title' => $offerSetGroupTitle,
            'advertiser_id' => $advertiserId
        ])->first();
        if (!$offerSetGroup) {
            $offerSetGroup = new OfferSetGroup();
            $offerSetGroup->title = $offerSetGroupTitle;
            $offerSetGroup->advertiser_id = $advertiserId;
            $offerSetGroup->save();
        }

        return $offerSetGroup;
    }
}
