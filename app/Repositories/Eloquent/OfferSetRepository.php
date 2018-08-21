<?php


namespace App\Repositories\Eloquent;


use App\Models\OfferSet;

class OfferSetRepository extends BaseRepository {

    public function modelClass()
    {
        return OfferSet::class;
    }

    /**
     * @param $offerSetGroupId
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getLiveOfferSets($offerSetGroupId, $startDate, $endDate)
    {
        return $this->model->join('offers', 'offers.offer_set_id', '=', 'offer_sets.id')
            ->join('images', 'images.offer_id', '=', 'offers.id')
            ->join('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->join('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->join('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_images.ad_id')
            ->where('offer_sets.offer_set_group_id', $offerSetGroupId)
            ->where('media_ads_insights.date', '>=', $startDate)
            ->where('media_ads_insights.date', '<=', $endDate)
            ->selectRaw('offer_sets.*, sum(media_ads_insights.spend) as spend')
            ->groupBy('offer_sets.id')
            ->having('spend', '>', 0)
            ->get();
    }
}