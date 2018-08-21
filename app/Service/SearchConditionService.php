<?php
namespace App\Service;

use App\Jobs\CrawlAccountPostsJob;
use App\Jobs\CrawlHashtagPostsJob;
use App\Models\Advertiser;
use App\Models\Hashtag;
use App\Models\Offer;
use App\Models\SearchCondition;
use App\Models\SearchHashtag;
use App\Repositories\Eloquent\AdvertiserRepository;
use App\Repositories\Eloquent\HashtagRepository;
use App\Repositories\Eloquent\SearchConditionRepository;
use App\Repositories\Eloquent\SearchHashtagRepository;

class SearchConditionService extends BaseService {

    CONST LIMIT_PER_ACCOUNT = 500;
    CONST LIMIT_IMAGE_PER_SEARCH_CONDITION = 50;

    /**
     * @var SearchConditionRepository
     */
    protected $repository;
    /**
     * @var SearchHashtagRepository
     */
    protected $searchHashtagRepository;

    public function __construct()
    {
        $this->repository = app(SearchConditionRepository::class);
        $this->searchHashtagRepository = app(SearchHashtagRepository::class);
    }

    /**
     * @param $searchConditionId
     * @param array $conditions
     * @param int $limit
     * @param bool $isCount
     * @param string $order
     * @param string $direction
     * @param bool $isExceptOffered
     * @return mixed
     */
    public function search($searchConditionId, $conditions = [], $limit = 20, $isCount = false, $order = 'pub_date', $direction = 'desc', $isExceptOffered = false)
    {
        if ($searchConditionId) {
            $searchConditionWithHashtag = $this->repository->findWithSearchHashtag($searchConditionId);

            return $this->searchHashtagRepository->search($searchConditionWithHashtag, $conditions, $limit, $isCount, $order, $direction, $isExceptOffered);

        } else {
            $advertiser = \Auth::guard('advertiser')->user();
            $searchConditions = $this->repository->getSearchConditionByAdvertiserId($advertiser->id, true);
            if ($isCount) {
                return $searchConditions->sum('post_count');
            }

            return $this->searchHashtagRepository->searchByMultiSearchConditions($searchConditions, $conditions, self::LIMIT_PER_ACCOUNT, $order, $direction);
        }
    }

    /**
     * @param $searchConditionId
     * @param array $status
     * @return array
     */
    public function statisticUGC($searchConditionId, $status = [])
    {
        $searchCondition = $this->repository->find($searchConditionId);

        $statistics = $this->searchHashtagRepository->getUGCStatistic($searchCondition, $status);

        $allCount = 0;
        $approvedCount = 0;
        $offeringCount = 0;
        $livingCount = 0;
        $failedCount = 0;
        $archivedCount = 0;
        foreach ($statistics as $statistic) {
            $allCount += $statistic->ugc_count;

            if ($statistic->status === Offer::STATUS_OFFERED || $statistic->status == Offer::STATUS_COMMENTED) {
                $offeringCount += $statistic->ugc_count;
            } elseif ($statistic->status == Offer::STATUS_APPROVED) {
                $approvedCount = $statistic->ugc_count;
            } elseif ($statistic->status == Offer::STATUS_LIVING) {
                $livingCount = $statistic->ugc_count;
            } elseif ($statistic->status == Offer::STATUS_COMMENT_FALSE) {
                $failedCount = $statistic->ugc_count;
            } elseif ($statistic->status == Offer::STATUS_ARCHIVE) {
                $archivedCount = $statistic->ugc_count;
            }
        }

        return [$allCount, $offeringCount, $approvedCount, $livingCount, $failedCount, $archivedCount];
    }

    /**
     * @param $advertiserId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSearchConditionList($advertiserId)
    {
        return $this->repository->findAllBy('advertiser_id', $advertiserId);
    }

    /**
     * @param $searchConditionId
     * @return mixed
     */
    public function getSearchConditionById($searchConditionId)
    {
        return $this->repository->find($searchConditionId);
    }

    /**
     * @param $searchConditionId
     * @param $advertiserId
     * @throws \Exception
     */
    public function deleteSearchCondition($searchConditionId, $advertiserId)
    {
        /** @var HashtagRepository $hashtagRepository */
        $hashtagRepository = app(HashtagRepository::class);

        \DB::beginTransaction();
        try{
            $searchCondition = $this->repository->find($searchConditionId);
            if (!$searchCondition || $searchCondition->advertiser_id != $advertiserId) {
                // invalid request
                throw new \Exception('Invalid Request');
            }

            $searchHashtags = $this->searchHashtagRepository->findAllBy('search_condition_id', $searchConditionId);

            foreach ($searchHashtags as $searchHashtag) {
                $count = $this->searchHashtagRepository->count('hashtag_id', $searchHashtag->hashtag_id);
                if ($count <= 1) {
                    $hashtagRepository->update(['active_flg' => Hashtag::UNACTIVE], $searchHashtag->hashtag_id);
                }
                $this->searchHashtagRepository->delete($searchHashtag->id);
            }

            $this->repository->delete($searchCondition->id);

            \DB::commit();
        }catch (\ErrorException $e) {
            \DB::rollBack();
            \Log::error($e);
        }
    }

    /**
     * @param $advertiserId
     * @param array $hashtags
     * @param string $searchConditionPrefix
     * @param bool $activeFlg
     * @param bool $crawlNow
     * @param int $hashtagType
     * @return mixed|static
     * @throws \Exception
     */
    public function createSearchCondition($advertiserId, $hashtags = [], $searchConditionPrefix = "#", $activeFlg = true, $crawlNow = true, $hashtagType = Hashtag::TYPE_HASHTAG)
    {
        /** @var AdvertiserRepository $advertiserRepository */
        $advertiserRepository = app(AdvertiserRepository::class);
        /** @var HashtagRepository $hashtagRepository */
        $hashtagRepository = app(HashtagRepository::class);

        //上限確認
        $advertiser = $advertiserRepository->find($advertiserId);
        $conditionCount = $this->repository->count('advertiser_id', $advertiserId);
        if ($conditionCount > $advertiser->max_search_condition) {
            throw new \Exception('登録可能数を超えました');
        }
        if (!count($hashtags)) {
            throw new \Exception('ハッシュタグを入力してください');
        }

        $hashtagArr = [];
        try {
            if ($hashtagType == Hashtag::TYPE_USER) {
                $title = $searchConditionPrefix . implode(' x ', $hashtags);
            } else {
                $title = $searchConditionPrefix . implode(' x #', $hashtags);
            }
            $searchConditionAttributes = [
                'title' => $title,
                'advertiser_id' => $advertiserId
            ];
            $searchCondition = $this->repository->queryWhere($searchConditionAttributes)->first();
            if ($searchCondition) {
                return $searchCondition;
            }

            \DB::beginTransaction();
            $newCondition = $this->repository->create($searchConditionAttributes);

            foreach ($hashtags as $hashtag) {
                $hashtag = trim($hashtag);
                if (!$hashtag) {
                    throw new \Exception('ハッシュタグが正しくありません');
                }
                $hashtagIdent = [
                    'hashtag' => $hashtag,
                    'type'    => $hashtagType
                ];
                $hashtagObj = $hashtagRepository->createOrUpdate($hashtagIdent, $hashtagIdent, ['active_flg' => $activeFlg]);

                $searchHashtagIdent = [
                    'search_condition_id' => $newCondition->id,
                    'hashtag_id' => $hashtagObj->id
                ];
                $this->searchHashtagRepository->createOrUpdate($searchHashtagIdent, $searchHashtagIdent);

                $hashtagArr[] = $hashtagObj;
            }

            \DB::commit();

            if ($crawlNow) {
                $this->realtimeCrawlHashtag($hashtagArr);
            }

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
        }

        return $newCondition;
    }
    
    /**
     * @param $accountId
     */
    public function realTimeCrawlAccount($accountId)
    {
        try {
            dispatch(new CrawlAccountPostsJob($accountId, 300));
            $advertiser = \Auth::guard('advertiser')->user();
            \Log::info("Advertiser id " . $advertiser->id . " registered account id " . $accountId);
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    /**
     * @param array $hashtags
     */
    public function realtimeCrawlHashtag($hashtags = [])
    {
        foreach ($hashtags as $hashtag) {
            try {
                dispatch(new CrawlHashtagPostsJob($hashtag->id, 200));
                $advertiser = \Auth::guard('advertiser')->user();
                \Log::info("Advertiser id " . $advertiser->id . " registered hashtag id " . $hashtag->id);
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }

    /**
     * @param $searchConditionId
     * @return mixed
     */
    public function countCrawlingHashtag($searchConditionId)
    {
        return $this->searchHashtagRepository->countCrawlingHashtag($searchConditionId);
    }

    /**
     * @param $hashtag
     */
    public function updateSearchConditionResultCount($hashtag)
    {
        $searchConditions = SearchHashtag::where('hashtag_id', $hashtag->id)->select('search_condition_id')->distinct()->get();
        foreach ($searchConditions as $searchCondition) {
            $count = $this->search($searchCondition->search_condition_id, [], 0, true);
            SearchCondition::findOrFail($searchCondition->search_condition_id)->update(['post_count' => $count]);
        }
    }

    /**
     * @param $adAccountId
     * @return mixed
     */
    public function countAllUgcByAdAccount($adAccountId)
    {
        return SearchCondition::where('advertiser_id', '=', $adAccountId)->sum('post_count');
    }

    /**
     * @param $advertiserId
     * @return array
     */
    public function countExceptOffer($advertiserId)
    {
        $list = [];
        $searchConditions = SearchCondition::where('advertiser_id', '=', $advertiserId)
            ->select('id')->get();
        foreach ($searchConditions as $searchCondition) {
            $count = $this->search($searchCondition->id, [], 0, true, 'pub_date', 'desc', true);
            $list[$searchCondition->id] = $count;
        }

        return $list;
    }

    /**
     * @param $advertiserId
     * @return string
     */
    public static function getDefaultSearchConditionTitle($advertiserId)
    {
        return 'letro_admin_'.$advertiserId;
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getDefaultSearchCondition($advertiserId)
    {
        return $this->repository->findBy('title', $this->getDefaultSearchConditionTitle($advertiserId));
    }

    /**
     * @param $searchConditionId
     * @return mixed
     */
    public function getHashtagBySearchConditionId($searchConditionId)
    {
        /** @var HashtagRepository $hashtagRepository */
        $hashtagRepository = app(HashtagRepository::class);

        return $hashtagRepository->getHashtagBySearchConditionId($searchConditionId);
    }
    
    public function getSearchConditionByHashtagIdAndAdvertiserId($hashtagId, $advertiserId) {
        $searchConditions = $this->repository->getSearchConditionByHashtagIdAndAdvertiserId($hashtagId, $advertiserId);
        
        return $searchConditions;
    }

    /**
     * @param $searchConditionId
     * @return mixed
     */
    public function getSearchHashtagsBySearchConditionId($searchConditionId)
    {
        return $this->searchHashtagRepository->findBy('search_condition_id', $searchConditionId);
    }

    public function increaseSearchConditionLimit($advertiserId)
    {
        $advertiserRepository = app(AdvertiserRepository::class);
        $advertiser = $advertiserRepository->find($advertiserId);
        $conditionCount = $this->repository->count('advertiser_id', $advertiserId);
        if ($conditionCount > $advertiser->max_search_condition) {
            $advertiser->max_search_condition = Advertiser::SEARCH_CONDITION_LIMIT;
            $advertiser->save();
        }
    }
}
