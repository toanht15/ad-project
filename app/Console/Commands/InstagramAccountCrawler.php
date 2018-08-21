<?php

namespace App\Console\Commands;

use App\Models\Advertiser;
use App\Models\Hashtag;
use App\Models\InstagramAccount;
use App\Models\Offer;
use App\Service\AdvertiserService;
use App\Service\HashtagService;
use App\Service\ImageService;
use App\Service\OfferService;
use App\Service\PostService;
use Classes\Constants;
use Classes\InstagramApiClient;
use App\Service\SearchConditionService;
use App\Service\InstagramAccountService;

class InstagramAccountCrawler extends BaseCommand
{
    const MAX_REQUESTS_PER_HOURS = 200;
    /** @var  InstagramApiClient */
    private $instagramClient;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagramAccountCrawler {limit?} {accountId?}';

    /** @var OfferService $offerService */
    protected $offerService;
    /** @var PostService $postService */
    protected $postService;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $limit = $this->argument('limit');
        $limit = $limit ? $limit : 300;
        $accountId = $this->argument('accountId');
    
        $this->instagramClient = new InstagramApiClient();
    
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        /** @var SearchConditionService $searchConditionService */
        $searchConditionService = app(SearchConditionService::class);
        /** @var InstagramAccountService $instagramAccountService */
        $instagramAccountService = app(InstagramAccountService::class);
        /** @var HashtagService $hashtagService */
        $hashtagService = app(HashtagService::class);
        $this->postService = app(PostService::class);
        $this->offerService = app(OfferService::class);

        $advertisers = $advertiserService->getWhere(['is_crawl_own_post' => true]);
        $instagramAccountCounter = 0;
        foreach ($advertisers as $advertiser) {
            $instagramAccounts = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id, $accountId);
            $instagramAccountCounter = $instagramAccountCounter + count($instagramAccounts);
            foreach ($instagramAccounts as $instagramAccount) {
                if (!$instagramAccount->username) {
                    continue;
                }
                try {
                    $this->createInstaAccountPostOffers($instagramAccount, $advertiser->id);
                    if (!$this->instagramClient->isValidToken($instagramAccount->access_token)) {
                        \Log::info('instagramAccountCrawler Token invalid ' . $instagramAccount->username);
                        $instagramAccountData = array(
                            'expired_token_flg' => InstagramAccount::EXPIRED_FLG_YES
                        );
                        $instagramAccountService->updateModel($instagramAccountData, $instagramAccount->id);
                        break;
                    }
                    $hashtag = $hashtagService->getWhere([
                        'hashtag' => $instagramAccount->username,
                        'active_flg' => Hashtag::ACTIVE
                    ])->first();

                    if (!$hashtag) {
                        continue;
                    }

                    if(($hashtag->last_crawled_at != '0000-00-00 00:00:00')) {
                        // 2回以上クロールの場合はlimitを100にする
                        $limit = 100;
                    }
                    $this->getInstagramPost($instagramAccount->instagram_id, $advertiser, $hashtag, $limit, count($instagramAccounts) * self::MAX_REQUESTS_PER_HOURS);
                    $hashtagService->updateModel(['last_crawled_at' => (new \DateTime())->format('Y-m-d H:i:s')], $hashtag->id);
                    $searchConditionService->updateSearchConditionResultCount($hashtag);

                } catch (\Exception $e) {
                    \Log::error($e);
                }
            }
        }
        \Log::info('Completed '.$instagramAccountCounter.' instagramAccounts');
    }

    /**
     * @param $instagramAccountId
     * @param $advertiser
     * @param $hashtag
     * @param int $max
     * @param null $maxRequest
     */
    private function getInstagramPost($instagramAccountId, $advertiser, $hashtag, $max, $maxRequest)
    {
        $count = 0;
        $requestCount = 0;
        $startTime = time();
        $postIds = [];
        $maxId = null;
        do {
            $responses = $this->instagramClient->getUserMedia($instagramAccountId, $maxId, $max);
            $maxId = $responses->getNextMaxId();
            $data = $responses->getData();

            foreach ($data as $postData) {
                $postType = $postData->getType();
                if (!PostService::isAcceptablePostType($postType)) {
                    continue;
                }

                $storedPostIds = $this->postService->storePostData($postData, $hashtag);
                $postIds = array_merge($postIds, $storedPostIds);

                $count ++;
                if ($count >= $max) {
                    break;
                }
            }

            $requestCount ++;
            if($requestCount == $maxRequest) {
                $executedTime = time() - $startTime;
                if($executedTime <= 3600) {
                    sleep(3700 - $executedTime);
                    $startTime = time();
                    $requestCount = 0;
                }
            }
        } while ($count < $max && $maxId);

        $this->offerService->createBulkDummyOffers($advertiser->id, $postIds, Offer::STATUS_APPROVED);

        \Log::debug('InstagramAccountCrawler@getInstagramPost instagramAccountId:'.$instagramAccountId.' '.$count);
    }

    /**
     * @param InstagramAccount $instagramAccount
     * @param $advertiserId
     */
    private function createInstaAccountPostOffers(InstagramAccount $instagramAccount, $advertiserId)
    {
        $postIds        = $this->postService->getPostsByAuthorName($instagramAccount->username)->pluck('id');
        $offeredPostIds = $this->postService->getPostWithOffers($postIds)->pluck('id')->unique();
        $unOfferPostIds = $postIds->diff($offeredPostIds)->all();
        $this->offerService->createBulkDummyOffers($advertiserId, $unOfferPostIds, Offer::STATUS_APPROVED);
    }
}
