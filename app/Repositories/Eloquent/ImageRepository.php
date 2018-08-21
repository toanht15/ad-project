<?php


namespace App\Repositories\Eloquent;


use App\Models\Image;
use App\Models\Post;

class ImageRepository extends BaseRepository {

    public function modelClass()
    {
        return Image::class;
    }

    /**
     * @param $offerId
     * @param $advertiserId
     * @return mixed
     */
    public function getImageListWithSummaryByOfferId($offerId, $advertiserId)
    {
        return $this->model->where('offer_id', $offerId)
            ->where('images.advertiser_id', $advertiserId)
            ->leftJoin('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->leftJoin('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->leftJoin('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_images.ad_id')
            ->groupBy('images.id')
            ->selectRaw('images.*,media_image_entries.hash_code,(sum(media_ads_insights.click)/sum(media_ads_insights.impression)) as ctr')
            ->get();
    }

    /**
     * @param $imageId
     * @return mixed
     */
    public function getImageKpi($imageId)
    {
        return $this->model->join('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->join('media_accounts', 'media_accounts.id', '=', 'media_image_entries.media_account_id')
            ->leftJoin('ads_use_images', 'ads_use_images.image_entry_id', '=', 'media_image_entries.id')
            ->leftJoin('media_ads_insights', 'media_ads_insights.facebook_ad_id', '=', 'ads_use_images.ad_id')
            ->where('images.id', $imageId)
            ->groupBy('media_accounts.id')
            ->selectRaw('media_type, media_accounts.name, sum(spend) as spend, sum(impression) as imp, sum(click) as click, sum(click)/sum(impression) as ctr')
            ->get();
    }

    /**
     * @param $postId
     * @param $advertiserId
     * @return mixed
     */
    public function getVideoByPostId($postId, $advertiserId)
    {
        return $this->model->join('offers', 'offers.id', '=', 'images.offer_id')
            ->join('posts', 'posts.id', '=', 'offers.post_id')
            ->select('images.id')
            ->where([
                'posts.id'             => $postId,
                'offers.advertiser_id' => $advertiserId,
                'images.file_format'   => Post::VIDEO
            ])
            ->first();
    }

    /**
     * @return mixed
     */
    public function getAllVideoWithPost()
    {
        return $this->model->join('offers', 'offers.id', '=', 'images.offer_id')
            ->join('posts', 'posts.id', '=', 'offers.post_id')
            ->selectRaw('images.id as video_id, images.video_url as image_video_url, posts.id as post_id, posts.video_url as post_video_url')
            ->where([
                'images.file_format'   => Post::VIDEO
            ])
            ->get();
    }
}