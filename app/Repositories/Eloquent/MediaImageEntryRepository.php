<?php


namespace App\Repositories\Eloquent;


use App\Models\MediaImageEntry;
use App\Models\Post;

class MediaImageEntryRepository extends BaseRepository {

    public function modelClass()
    {
        return MediaImageEntry::class;
    }

    public function getFBImageEntryWithImageInfo($mediaAccountId)
    {
        return $this->model->join('images', 'images.id', '=', 'media_image_entries.image_id')
            ->where('media_image_entries.media_account_id', $mediaAccountId)
            ->whereNotNull('images.offer_id')
            ->select('media_image_entries.*')
            ->get();
    }

    /**
     * @param $postId
     * @param $mediaAccountId
     * @return mixed
     */
    public function getFBImgEntryByPostId($postId, $mediaAccountId)
    {
        return $this->model->join('images', 'images.id', '=', 'media_image_entries.image_id')
            ->join('offers', 'offers.id', '=', 'images.offer_id')
            ->join('posts', 'posts.id', '=', 'offers.post_id')
            ->where([
                'posts.id'                             => $postId,
                'media_image_entries.media_account_id' => $mediaAccountId,
                'posts.file_format'                    => Post::VIDEO
            ])
            ->select('media_image_entries.id')
            ->first();
    }
}