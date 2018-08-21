<?php


namespace App\Repositories\Eloquent;


use App\Models\Slideshow;

class SlideshowRepository extends BaseRepository {

    public function modelClass()
    {
        return Slideshow::class;
    }

    public function getImagesBySlideshowId($slideshowId)
    {
        return $this->model->join('slideshow_images', 'slideshows.id', '=', 'slideshow_images.slideshow_id')
            ->join('images', 'images.id', '=', 'slideshow_images.image_id')
            ->join('offers', 'offers.id', '=', 'images.offer_id')
            ->selectRaw('offers.post_id, images.image_url, images.id as image_id')
            ->where('slideshows.id', '=', $slideshowId)
            ->get();
    }

    /**
     * @return mixed
     */
    public function getActiveSlideshowWithKpi($advertiserId, $beginDate, $endDate)
    {
        $query = $this->model->join('media_account_slideshows', 'media_account_slideshows.slideshow_id', '=', 'slideshows.id')
            ->join('ads_use_slideshows', 'media_account_slideshows.id', '=', 'ads_use_slideshows.media_slideshow_id')
            ->join('media_ads_insights', 'ads_use_slideshows.ad_id', '=', 'media_ads_insights.facebook_ad_id')
            ->join('slideshow_images', 'slideshow_images.slideshow_id', '=', 'slideshows.id')
            ->join('images', 'images.id', '=', 'slideshow_images.image_id')
            ->join('offers', 'offers.id', '=', 'images.offer_id')
            ->join('posts', 'posts.id', '=', 'offers.post_id')
            ->selectRaw('offers.id as offer_id, sum(media_ads_insights.spend) as spend')
            ->whereBetween('media_ads_insights.date', [$beginDate, $endDate])
            ->where('slideshows.advertiser_id', '=', $advertiserId)
            ->where('spend', '>', 0)
            ->groupBy('offers.id');

        return $query->get();
    }

    /**
     * @param $slideshowId
     * @return mixed
     */
    public function getSlideshowTotalKpi($slideshowId)
    {
        return $this->model->leftJoin('media_account_slideshows', 'media_account_slideshows.slideshow_id', '=', 'slideshows.id')
            ->leftJoin('ads_use_slideshows', 'media_account_slideshows.id', '=', 'ads_use_slideshows.media_slideshow_id')
            ->leftJoin('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_slideshows.ad_id')
            ->leftJoin('media_accounts', 'media_accounts.id', '=', 'media_account_slideshows.media_account_id')
            ->groupBy('media_accounts.id')
            ->where([
                'slideshows.id' => $slideshowId,
            ])->selectRaw('media_accounts.media_type, media_accounts.name, sum(spend) as spend, sum(impression) as imp, sum(click) as click, sum(click)/sum(impression) as ctr')
            ->get();
    }
}