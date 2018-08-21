<?php


namespace App\Repositories\Eloquent;


use App\Models\ArchivedPost;

class ArchivedPostRepository extends BaseRepository {

    public function modelClass()
    {
        return ArchivedPost::class;
    }

    /**
     * @param $adAccountId
     * @param bool $isCount
     * @param int $limit
     * @param string $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|int
     */
    public function getArchivedPost($adAccountId, $isCount = false, $limit = 20, $order = 'archived_posts.created_at')
    {
        $query = $this->model->where('advertiser_id', $adAccountId);
        if ($isCount) {
            return $query->count('id');
        }

        return $query->join('posts', 'posts.id', '=', 'archived_posts.post_id')
            ->join('authors', 'authors.id', '=', 'posts.author_id')
            ->select([
                'posts.id as post_id',
                'posts.admin_approved_flg',
                'posts.image_url',
                'posts.post_url',
                'posts.file_format',
                'posts.video_url',
                'posts.text',
                'posts.like',
                'posts.pub_date',
                'authors.profile_url as author_url',
                'authors.name as author_name',
                'authors.username',
                'authors.icon_img as author_icon_img',
                'authors.post_count as author_post_count',
                'authors.follower as author_follower',
            ])->orderBy($order, 'desc')->paginate($limit);
    }

    /**
     * @param $advertiserId
     * @param $postIds
     * @return mixed
     */
    public function unArchivedPosts($advertiserId, $postIds)
    {
        return $this->model->where('advertiser_id', $advertiserId)->whereIn('post_id', $postIds)->delete();
    }
}