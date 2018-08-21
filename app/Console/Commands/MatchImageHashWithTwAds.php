<?php

namespace App\Console\Commands;

use App\Models\AdsUseImage;
use App\Models\AdsUseSlideshow;
use App\Models\MediaAccount;
use App\Models\Slideshow;
use App\Service\AdsUseMaterialService;
use App\Service\AdsUseSlideshowService;
use App\Service\MediaImageEntryService;
use App\Service\MediaAccountService;
use App\Service\MediaAccountSlideshowService;
use App\Service\MediaAdService;
use App\Service\SlideshowService;
use Classes\Constants;
use Classes\TwitterApiClient;
use Hborras\TwitterAdsSDK\TwitterAdsException;

class MatchImageHashWithTwAds extends BaseCommand
{

    const OBJECT_TYPE_IMAGE     = 1;
    const OBJECT_TYPE_SLIDESHOW = 2;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matchImageHashWithTwAds';

    /** @var MediaAdService $mediaAdService */
    protected $mediaAdService;
    /** @var AdsUseMaterialService $adUseMaterialService */
    protected $adUseMaterialService;
    /** @var AdsUseSlideshowService $adUseSlishowService */
    protected $adUseSlishowService;

    /** @var  TwitterApiClient */
    private $twClient;

    public function doCommand()
    {
        $this->twClient = TwitterApiClient::createInstance();
        $this->mediaAdService = app(MediaAdService::class);
        $this->adUseMaterialService = app(AdsUseMaterialService::class);
        $this->adUseSlishowService = app(AdsUseSlideshowService::class);
        /** @var MediaAccountService $mediaAccountService */
        $mediaAccountService = app(MediaAccountService::class);
        /** @var MediaImageEntryService $mediaImageEntryService */
        $mediaImageEntryService = app(MediaImageEntryService::class);
        /** @var MediaAccountSlideshowService $mediaSlideshowService */
        $mediaSlideshowService = app(MediaAccountSlideshowService::class);

        /** @var MediaAccount $mediaAccounts */
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken();
        foreach ($mediaAccounts as $mediaAccount) {
            if ($mediaAccount->media_type != Constants::MEDIA_TWITTER) {
                continue;
            }

            try {
                $this->twClient->setOauthToken($mediaAccount->access_token, $mediaAccount->refresh_token);
                $this->twClient->setAccountId($mediaAccount->media_account_id);

                $images = $mediaImageEntryService->getFBImageEntryWithImageInfo($mediaAccount->id);
                $slideshows = $mediaSlideshowService->getMediaSlideshowByMediaId($mediaAccount->id);

                if (!$images->count() && !$slideshows->count()) {
                    // 同期された画像またはスライドショーがない場合
                    continue;
                }

                $promotedTweets = $this->twClient->getPromotedTweet(['sort_by' => 'created_at-desc', 'count' => 100]);

                if (!$promotedTweets->count()) {
                    continue;
                }

                foreach ($promotedTweets as $promotedTweet) {
                    try {
                        if ($mediaAccount->last_crawled_ad_id && $mediaAccount->last_crawled_ad_id == $promotedTweet->getId()) {
                            continue;
                        }
                        $this->matchPromotedTweetWithImage($mediaAccount, $promotedTweet, $images, $slideshows);

                    } catch (\Exception $e) {
                        \Log::error($e);
                    }
                }

                // update last ads id
                if (isset($promotedTweets[0])) {
                    $mediaAccountService->updateModel(['last_crawled_ad_id' => $promotedTweets[0]->getId()], $mediaAccount->id);
                }

            } catch (TwitterAdsException $e) {
                \Log::error($e->getErrors());
            } catch (\Exception $e) {
                \Log::error('matchImageHashWithAds adAccountId:' . $mediaAccount->id);
                \Log::error($e);
            }
        }
    }

    /**
     * @param $mediaAccount
     * @param $promotedTweet
     * @param $images
     * @param $slideshows
     */
    private function matchPromotedTweetWithImage($mediaAccount, $promotedTweet, $images, $slideshows)
    {
        $tweetId = $promotedTweet->getTweetId();

        // match tweet with Letro image
        $image = $this->matchTweetWithEntity($tweetId, $images, 'hash_code', $mediaAccount->id, $promotedTweet->getId());
        if ($image) {
            return;
        }
        // match tweet with slideshow
        $slideshow = $this->matchTweetWithEntity($tweetId, $slideshows, 'media_object_id', $mediaAccount->id, $promotedTweet->getId(), self::OBJECT_TYPE_SLIDESHOW);
        if ($slideshow) {
            return;
        }

        // if tweet is not match with image and slideshow, get the tweet card and match again
        $tweet = $this->twClient->getTweet($tweetId);
        // check if tweet has card
        try {
            $tweetBody = $tweet->getBody();
            if (!isset($tweetBody->entities->urls[0]->expanded_url)) {
                // check if card url is existed
                return;
            }
            $expandedUrl = $tweetBody->entities->urls[0]->expanded_url;
            if (!strpos($expandedUrl, 'cards.twitter.com')) {
                // check invalid card url
                return;
            }

            $urlPath = parse_url($expandedUrl, PHP_URL_PATH);
            $cardId = explode('/',$urlPath)[3];
            // match card with Letro image
            $image = $this->matchTweetWithEntity($cardId, $images, 'hash_code', $mediaAccount->id, $promotedTweet->getId());
            if ($image) {
                return;
            }
            // match card with slideshow
            $slideshow = $this->matchTweetWithEntity($cardId, $slideshows, 'media_object_id', $mediaAccount->id, $promotedTweet->getId(), self::OBJECT_TYPE_SLIDESHOW);
            if ($slideshow) {
                return;
            }

        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    /**
     * @param $mediaEntityId
     * @param $entities
     * @param $attribute
     * @param $mediaAccountId
     * @param $promotedTweetId
     * @param int $entityType
     * @return null
     */
    private function matchTweetWithEntity($mediaEntityId, $entities, $attribute, $mediaAccountId, $promotedTweetId, $entityType = self::OBJECT_TYPE_IMAGE)
    {
        //検索頻度が高いのでクエリを使わない
        foreach ($entities as $entity) {
            if ($entity->$attribute == $mediaEntityId) {
                $this->storeAds($mediaAccountId, $promotedTweetId, $entity, $entityType);
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param $mediaAccountId
     * @param $adId
     * @param $object
     * @param int $objectType
     */
    public function storeAds($mediaAccountId, $adId, $object, $objectType = self::OBJECT_TYPE_IMAGE)
    {
        $ad = $this->mediaAdService->createOrUpdate(
            [
                'ad_id' => $adId
            ],
            [
                'ad_id' => $adId,
                'media_account_id' => $mediaAccountId
            ]
        );

        if ($objectType === self::OBJECT_TYPE_IMAGE) {
            $this->adUseMaterialService->createOrUpdate(
                [
                    'ad_id' => $ad->id,
                    'image_entry_id' => $object->id
                ],
                [
                    'ad_id' => $ad->id,
                    'image_entry_id' => $object->id
                ]
            );

        } elseif ($objectType === self::OBJECT_TYPE_SLIDESHOW) {
            $this->adUseSlishowService->createOrUpdate(
                [
                    'ad_id' => $ad->id,
                    'media_slideshow_id' => $object->id
                ],
                [
                    'ad_id' => $ad->id,
                    'media_slideshow_id' => $object->id
                ]
            );
        }
    }
}
