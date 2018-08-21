<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaAccountSlideshow;

class MediaAccountSlideshowRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaAccountSlideshow::class;
    }

    /**
     * @param $mediaId
     * @return mixed
     */
    public function getMediaSlideshowByMediaId($mediaId)
    {
        return $this->model->join('media_accounts', 'media_accounts.id', '=', 'media_account_slideshows.media_account_id')
            ->where('media_accounts.id', $mediaId)
            ->select('media_account_slideshows.*')
            ->get();
    }
}