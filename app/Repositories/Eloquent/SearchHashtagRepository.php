<?php


namespace App\Repositories\Eloquent;


use App\Models\Hashtag;
use App\Models\SearchHashtag;

class SearchHashtagRepository extends BaseRepository {

    public function modelClass()
    {
        return SearchHashtag::class;
    }

    /**
     * @param $searchConditionWithHashtag
     * @param array $conditions
     * @return mixed
     */
    public function getSearchBaseQuery($searchConditionWithHashtag, $conditions = [])
    {
        $searchHashtags = $searchConditionWithHashtag->searchHashtags;
        $firstHashtagCondition = $searchHashtags->shift();
        $query = $this->model->where('search_hashtags.search_condition_id', $searchConditionWithHashtag->id);
        //最初のハッシュタグ
        $query = $query->join('hashtag_has_post', 'hashtag_has_post.hashtag_id', '=', \DB::raw($firstHashtagCondition->hashtag_id))
            ->join('posts', 'posts.id', '=', 'hashtag_has_post.post_id')->whereNull('posts.deleted_at');

        if ($searchHashtags->count()) {
            //複数ハッシュタグを組み合わせ
            foreach ($searchHashtags as $searchHashtag) {
                $hashtagId = $searchHashtag->hashtag_id;
                $query = $query->join('hashtag_has_post as hashtag_'.$hashtagId, function ($join) use ($hashtagId) {
                    $join->on('hashtag_'.$hashtagId.'.hashtag_id', '=', \DB::raw($hashtagId));
                    $join->on('hashtag_'.$hashtagId.'.post_id', '=', 'posts.id');
                });
            }
        }

        $query = $query->join('authors', 'authors.id', '=', 'posts.author_id')
            ->whereNotIn('posts.id', function ($query) use ($searchConditionWithHashtag) {
                $query->select('post_id')->from('archived_posts')->where('advertiser_id', $searchConditionWithHashtag->advertiser_id);
            })
            ->leftJoin('offers', function ($join) use ($searchConditionWithHashtag) {
                $join->on('offers.post_id', '=', 'posts.id');
                $join->on(\DB::raw('(offers.advertiser_id is null or offers.advertiser_id = '.$searchConditionWithHashtag->advertiser_id.')'), \DB::raw(''), \DB::raw(''));
            })
            ->leftJoin('part_images_temporaries', function ($join) use ($searchConditionWithHashtag, $conditions) {
                $join->on('part_images_temporaries.post_id', '=', 'posts.id');
                $join->on('part_images_temporaries.vtdr_part_id', '!=', \DB::raw(0));
                if (isset($conditions['siteId'])) {
                    $join->on('part_images_temporaries.vtdr_site_id', '=', \DB::raw($conditions['siteId']));
                } else {
                    //siteIdがない場合はadvertiserと紐ずけたsearch conditionで検索
                    $join->on('part_images_temporaries.search_condition_id', '=', \DB::raw($searchConditionWithHashtag->id));
                }
            });

        if (isset($conditions['status']) && count($conditions['status']) > 0) {
            $query = $query->whereIn('offers.status', $conditions['status']);
        }

        if (isset($conditions['partIds']) && count($conditions['partIds']) > 0) {
            $query = $query->whereIn('part_images_temporaries.vtdr_part_id', $conditions['partIds']);
        }

        return $query;
    }

    /**
     * @param $searchConditionWithHashtag
     * @param array $conditions
     * @return mixed
     */
    public function getCountBaseQuery($searchConditionWithHashtag, $conditions = [])
    {
        $searchHashtags = $searchConditionWithHashtag->searchHashtags;
        $firstHashtagCondition = $searchHashtags->shift();
        $query = $this->model->where('search_condition_id', $searchConditionWithHashtag->id);
        //最初のハッシュタグ
        $query = $query->join('hashtag_has_post', 'hashtag_has_post.hashtag_id', '=', \DB::raw($firstHashtagCondition->hashtag_id));

        if ($searchHashtags->count()) {
            //複数ハッシュタグを組み合わせ
            foreach ($searchHashtags as $searchHashtag) {
                $hashtagId = $searchHashtag->hashtag_id;
                $query = $query->join('hashtag_has_post as hashtag_'.$hashtagId, function ($join) use ($hashtagId) {
                    $join->on('hashtag_'.$hashtagId.'.hashtag_id', '=', \DB::raw($hashtagId));
                    $join->on('hashtag_'.$hashtagId.'.post_id', '=', 'hashtag_has_post.post_id');
                });
            }
        }

        $query = $query->whereNotIn('hashtag_has_post.post_id', function ($query) use ($searchConditionWithHashtag) {
                $query->select('post_id')->from('archived_posts')->where('advertiser_id', $searchConditionWithHashtag->advertiser_id);
            })
            ->leftJoin('offers', function ($join) use ($searchConditionWithHashtag) {
                $join->on('offers.post_id', '=', 'hashtag_has_post.post_id');
                $join->on(\DB::raw('(offers.advertiser_id is null or offers.advertiser_id = '.$searchConditionWithHashtag->advertiser_id.')'), \DB::raw(''), \DB::raw(''));
            });

        if (isset($conditions['status']) && count($conditions['status']) > 0) {
            $query = $query->whereIn('offers.status', $conditions['status']);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getImageColumnList()
    {
        return [
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
            'offers.id as offer_id',
            'offers.created_at as offer_created_at',
            'offers.user_id as offer_user_id',
            'offers.advertiser_id as offer_advertiser_id',
            'offers.status as offer_status',
            'images.id as image_id',
            'vtdr_part_id'
        ];
    }

    /**
     * @param $searchConditionWithHashtag
     * @param array $conditions
     * @param int $limit
     * @param bool $isCount
     * @param string $order
     * @param string $direction
     * @param bool $isExceptOffered
     * @return mixed
     */
    public function search($searchConditionWithHashtag, $conditions = [], $limit = 20, $isCount = false, $order = 'pub_date', $direction = 'desc', $isExceptOffered = false)
    {
        $select = $this->getImageColumnList();

        if ($isCount) {
            $query = $this->getCountBaseQuery($searchConditionWithHashtag, $conditions);
        } else {
            $query = $this->getSearchBaseQuery($searchConditionWithHashtag, $conditions);
            $query = $query->leftJoin('images', 'images.offer_id', '=', 'offers.id');
        }

        if ($isExceptOffered) {
            $query = $query->where('offers.id', '=', null);
        }

        if ($isCount) {
            return $query->distinct('hashtag_has_post.post_id')->count('hashtag_has_post.post_id');
        } else {
            $query = $query->groupBy('posts.id')->orderBy($order, $direction)->select($select);
            return $query->paginate($limit);
        }
    }

    /**
     * @param $searchConditions
     * @param array $conditions
     * @param int $limit
     * @param string $order
     * @param string $direction
     * @return mixed
     */
    public function searchByMultiSearchConditions($searchConditions, $conditions = [], $limit = 20, $order = 'pub_date', $direction = 'desc')
    {
        $select = $this->getImageColumnList();
        foreach ($searchConditions as $searchCondition) {
            $subQuery = $this->getSearchBaseQuery($searchCondition, $conditions)->select($select)->take($limit);
            $subQuery = $subQuery->leftJoin('images', 'images.offer_id', '=', 'offers.id')->groupBy('posts.id')->orderBy($order, $direction);
            if (isset($query)) {
                $query = $query->union($subQuery);
            } else {
                $query = $subQuery;
            }
        }

        $query = $query->orderBy($order, $direction);

        return $query->get();
    }

    /**
     * @param $searchConditionId
     * @return mixed
     */
    public function countCrawlingHashtag($searchConditionId)
    {
        return $this->model->where('search_condition_id', $searchConditionId)
            ->join('hashtags', function ($join) {
                $join->on('hashtags.id', '=', 'search_hashtags.hashtag_id');
                $join->on(\DB::raw('hashtags.active_flg='.Hashtag::CRAWLING), \DB::raw(''), \DB::raw(''));
            })->count();
    }

    /**
     * @param $searchCondition
     * @param $status
     * @return mixed
     */
    public function getUGCStatistic($searchCondition, $status)
    {
        $query = $this->getCountBaseQuery($searchCondition, $status);

        return $query->selectRaw('offers.status, count(distinct hashtag_has_post.post_id) as ugc_count')->groupBy('offers.status')->get();
    }
}