<?php

namespace App\Console\Commands;

use App\Models\Hashtag;
use App\Models\InstagramAccount;
use App\Repositories\Eloquent\PostRepository;
use App\Service\HashtagService;
use App\Service\ImageService;
use App\Service\PostService;
use App\Service\SearchConditionService;
use App\UGCConfig;
use Classes\Constants;
use Classes\InstagramApiClient;
use App\Models\ContractService;

class InstagramHashtagCrawler extends BaseCommand
{
    /** @var  InstagramApiClient */
    private $instagramClient;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagramHashtagCrawler {hashtagId?} {limit?}';

    /** @var PostService */
    protected $postService;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $hashtagId = $this->argument('hashtagId');
        $limit     = $this->argument('limit');
        /** @var HashtagService $hashtagService */
        $hashtagService = app(HashtagService::class);
        /** @var SearchConditionService $searchService */
        $searchService = app(SearchConditionService::class);
        /** @var PostService postService */
        $this->postService = app(PostService::class);

        $hashtags              = $hashtagService->getShouldCrawlHashtag($hashtagId);
        $this->instagramClient = new InstagramApiClient();
        foreach ($hashtags as $hashtag) {
            if (!$hashtagService->hasActiveContract($hashtag->id, [ContractService::FOR_AD, ContractService::FOR_OWNED])) {
                continue;
            }

            $hashtag->active_flg = Hashtag::CRAWLING;
            $hashtag->save();

            $this->setInstagramAccessToken($hashtag->id);
            try {
                $this->getInstagramPost($hashtag, $limit ? $limit : 100);

                $searchService->updateSearchConditionResultCount($hashtag);
            } catch (\Exception $e) {
                $hashtag->active_flg = Hashtag::FAIL;
                $hashtag->save();
                \Log::error($e);
            }
            $hashtag->last_crawled_at = (new \DateTime())->format('Y-m-d H:i:s');
            $hashtag->active_flg = $hashtag->active_flg == Hashtag::FAIL ? Hashtag::FAIL : Hashtag::ACTIVE;
            $hashtag->save();
        }
        \Log::info('Completed '.(count($hashtags)).' hashtags');
    }

    /**
     * @param Hashtag $hashtag
     * @param int $max
     * @param null $maxTagId
     */
    private function getInstagramPost(Hashtag $hashtag, $max = 100, $maxTagId = null)
    {
        $count = 0;
        $requestCount = 0;
        do {
            $responses = $this->instagramClient->getTagMedia($hashtag->hashtag, $maxTagId, $max);
            $maxTagId = $responses->getNextMaxTagId();

            $data = $responses->getData();

            foreach ($data as $postData) {
                $postType = $postData->getType();
                if (!PostService::isAcceptablePostType($postType)) {
                    continue;
                }

                $this->postService->storePostData($postData, $hashtag);
                $count ++;

                if ($count >= $max) {
                    break;
                }
            }

            $requestCount ++;
        } while ($count < $max && $maxTagId && $requestCount < 50);

        \Log::debug('InstagramHashtagCrawler@getInstagramPost hashtag:'.$hashtag->hashtag.' '.$count);
    }

    /**
     * @param Hashtag $hashtag
     */
    private function setInstagramAccessToken($hashtagId)
    {
        $hashtagService = new HashtagService();
        $instagramAccount = $hashtagService->getHashtagInstagramAccount($hashtagId);
        if (!$instagramAccount || !$this->instagramClient->isValidToken($instagramAccount->access_token)) {
            $instagramAccount = InstagramAccount::find(UGCConfig::get('instagram.crawlAccountId'));
        }
        $this->instagramClient->setToken($instagramAccount->access_token);
    }
}
