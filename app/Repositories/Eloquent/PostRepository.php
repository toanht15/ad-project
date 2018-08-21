<?php


namespace App\Repositories\Eloquent;


use App\Models\Post;

class PostRepository extends BaseRepository {

    public function modelClass()
    {
        return Post::class;
    }

    /**
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function getPostAuthor($id, $attribute = 'id')
    {
        return $this->model->join('authors', 'authors.id', '=', 'posts.author_id')
            ->where($attribute, $id)
            ->select('authors.*')
            ->first();
    }

    /**
     * @param $advertiserId
     * @param $excludeHashCodeList
     * @return mixed
     */
    public function getBannerPostIds($advertiserId, $excludeHashCodeList)
    {
        return $this->model->join('offers', 'offers.post_id', '=', 'posts.id')
            ->join('images', 'offers.id', '=', 'images.offer_id')
            ->leftJoin('media_image_entries', 'media_image_entries.image_id', '=', 'images.id')
            ->where('posts.post_id', '=', '')
            ->where(function($query) use ($excludeHashCodeList) {
                $query->whereNotIn('media_image_entries.hash_code', $excludeHashCodeList);
                $query->orWhereNull('media_image_entries.hash_code');
            })
            ->where('offers.advertiser_id', $advertiserId)
            ->distinct('posts.id')
            ->pluck('posts.id');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPostHashtagData($id)
    {
        $select = [
            'posts.id as post_id',
            'posts.post_id as post_media_id',
            'posts.file_format as type',
            'posts.image_url',
            'posts.post_url',
            'posts.pub_date',
            'posts.text',
            'posts.carousel_no',
            'authors.media_id as user_media_id',
            'authors.name as name',
            'authors.username as user_name',
            'hashtags.hashtag'
        ];
        return $this->model->join('authors', 'authors.id', '=', 'posts.author_id')
            ->join('hashtag_has_post', 'hashtag_has_post.post_id', '=', 'posts.id')
            ->join('hashtags', 'hashtags.id', '=', 'hashtag_has_post.hashtag_id')
            ->where('posts.id', '=', $id)
            ->select($select)
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPostWithAuthor($id)
    {
        return $this->model->join('authors', 'authors.id', '=', 'posts.author_id')
            ->where('posts.id', $id)
            ->select('posts.*','authors.name', 'authors.username', 'authors.profile_url as author_url')
            ->first();
    }

    /**
     * @param $postIds
     * @return mixed
     */
    public function getPostGroupByMediaId($postIds)
    {
        return $this->model->whereIn('id', $postIds)
            ->select(['id', 'post_id'])
            ->groupBy('post_id')
            ->get();
    }

    /**
     * @param $authorName
     * @return mixed
     */
    public function getPostsByAuthorName($authorName)
    {
        return $this->model->join('authors', 'authors.id', '=', 'posts.author_id')
            ->where('authors.username', '=', $authorName)
            ->select('posts.id')
            ->get();
    }

    /**
     * @param $postIds
     * @return mixed
     */
    public function getPostWithOffers($postIds)
    {
        return $this->model->join('offers', 'offers.post_id', '=', 'posts.id')
            ->whereIn('posts.id', $postIds)
            ->select('posts.*')
            ->get();
    }
}