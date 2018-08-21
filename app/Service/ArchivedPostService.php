<?php


namespace App\Service;


use App\Repositories\Eloquent\ArchivedPostRepository;

class ArchivedPostService extends BaseService {

    /**
     * @var ArchivedPostRepository
     */
    protected $repository;

    public function __construct(ArchivedPostRepository $archivedPost)
    {
        $this->repository = $archivedPost;
    }

    /**
     * @param $advertiserId
     * @param bool $isCount
     * @param int $limit
     * @param string $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|int
     */
    public function getArchivedPost($advertiserId, $isCount = false, $limit = 20, $order = 'archived_posts.created_at')
    {
        return $this->repository->getArchivedPost($advertiserId, $isCount, $limit, $order);
    }

    /**
     * @param $advertiserId
     * @param $postIds
     * @return mixed
     */
    public function unArchivedPosts($advertiserId, $postIds)
    {
        return $this->repository->unArchivedPosts($advertiserId, $postIds);
    }
}