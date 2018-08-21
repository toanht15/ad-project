<?php

namespace App\Console\Commands;

use App\Models\AdsUseImage;
use App\Models\AdsUseSlideshow;
use App\Models\ContractService;
use App\Models\Slideshow;
use App\Service\AdsUseMaterialService;
use App\Service\AdsUseSlideshowService;
use App\Service\AdvertiserService;
use App\Service\MediaImageEntryService;
use App\Service\MediaAccountService;
use App\Service\MediaAccountSlideshowService;
use App\Service\MediaAdService;
use App\Service\SlideshowService;
use Classes\Constants;
use Classes\FacebookGraphClient;
use FacebookAds\Object\Fields\AdCreativeFields;
use FacebookAds\Object\Fields\AdCreativeLinkDataFields;
use FacebookAds\Object\Fields\AdCreativeObjectStorySpecFields;
use FacebookAds\Object\Fields\AdCreativeVideoDataFields;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\Values\AdCreativeObjectTypeValues;

class MatchImageHashWithFbAds extends BaseCommand
{

    const OBJECT_TYPE_IMAGE     = 1;
    const OBJECT_TYPE_SLIDESHOW = 2;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matchImageHashWithFbAds';

    /** @var MediaAdService $mediaAdService */
    protected $mediaAdService;
    /** @var AdsUseMaterialService $adUseMaterialService */
    protected $adUseMaterialService;
    /** @var AdsUseSlideshowService $adUseSlishowService */
    protected $adUseSlishowService;

    /** @var  FacebookGraphClient */
    private $facebook;

    public function doCommand()
    {
        $this->facebook = new FacebookGraphClient();
        $this->mediaAdService = app(MediaAdService::class);
        $this->adUseMaterialService = app(AdsUseMaterialService::class);
        $this->adUseSlishowService = app(AdsUseSlideshowService::class);
        /** @var MediaAccountService $mediaAccountService */
        $mediaAccountService = app(MediaAccountService::class);
        /** @var MediaImageEntryService $mediaImageEntryService */
        $mediaImageEntryService = app(MediaImageEntryService::class);
        /** @var MediaAccountSlideshowService $mediaSlideshowService */
        $mediaSlideshowService = app(MediaAccountSlideshowService::class);
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken();

        foreach ($mediaAccounts as $mediaAccount) {
            if ($mediaAccount->media_type != Constants::MEDIA_FACEBOOK || $mediaAccount->token_expired_flg || !$advertiserService->hasActiveContract($mediaAccount->advertiser_id, ContractService::FOR_AD)) {
                continue;
            }

            try {
                if (!$this->facebook->setAccessTokenByMediaAccount($mediaAccount)) {
                    continue;
                }

                $images = $mediaImageEntryService->getFBImageEntryWithImageInfo($mediaAccount->id);
                $slideshows = $mediaSlideshowService->getMediaSlideshowByMediaId($mediaAccount->id);

                if (!$images->count() && !$slideshows->count()) {
                    continue;
                }

                $response = $this->facebook->getAdsByAccountId($mediaAccount->media_account_id, [
                        'fields' => AdFields::CREATIVE,
                        'limit' => 100
                    ]
                );

                if (!isset($response['data'])) {
                    continue;
                }

                $adCreativeMap = [];
                foreach ($response['data'] as $data) {
                    //convert data to creative ids array
                    if ($mediaAccount->last_crawled_ad_id && $mediaAccount->last_crawled_ad_id == $data[AdFields::ID]) {
                        break;
                    }
                    $creativeId = $data[AdFields::CREATIVE][AdCreativeFields::ID];
                    $adCreativeMap[$creativeId][] = $data[AdFields::ID];
                }

                $this->matchAdCreative($mediaAccount->id, $adCreativeMap, $images, $slideshows);

                if (isset($response['data'][0][AdFields::ID])) {
                    $mediaAccount->last_crawled_ad_id = $response['data'][0][AdFields::ID];
                    $mediaAccount->save();
                }
            } catch (\Exception $e) {
                \Log::error('matchImageHashWithAds adAccountId:' . $mediaAccount->id);
                \Log::error($e);
            }
        }
    }

    /**
     * @param $mediaAccountId
     * @param $creativeMap
     * @param $images
     * @param $slideshows
     */
    public function matchAdCreative($mediaAccountId, $creativeMap, $images, $slideshows)
    {
        $creativeIds = array_keys($creativeMap);
        $creatives = $this->facebook->getDataByBatchRequest($creativeIds, '', [
            'fields' => AdCreativeFields::IMAGE_HASH.','.AdCreativeFields::OBJECT_TYPE.','.AdCreativeFields::OBJECT_STORY_SPEC.','.AdCreativeFields::VIDEO_ID
        ]);

        foreach ($creatives as $creative) {
            if ($creative[AdCreativeFields::OBJECT_TYPE] == AdCreativeObjectTypeValues::VIDEO) {
                //video ads
                $videoId = null;
                if (isset($creative[AdCreativeFields::OBJECT_STORY_SPEC][AdCreativeObjectStorySpecFields::VIDEO_DATA][AdCreativeVideoDataFields::VIDEO_ID])) {
                    $videoId = $creative[AdCreativeFields::OBJECT_STORY_SPEC][AdCreativeObjectStorySpecFields::VIDEO_DATA][AdCreativeVideoDataFields::VIDEO_ID];
                } else if (isset($creative[AdCreativeFields::VIDEO_ID])) {
                    $videoId = $creative[AdCreativeFields::VIDEO_ID];
                }
                if ($videoId && $slideshow = $this->findSlideshowByFBId($videoId, $slideshows)) {
                    $this->storeAds($mediaAccountId, $creativeMap[$creative[AdCreativeFields::ID]], $slideshow, self::OBJECT_TYPE_SLIDESHOW);
                }

            } elseif (isset($creative[AdCreativeFields::OBJECT_STORY_SPEC][AdCreativeObjectStorySpecFields::LINK_DATA][AdCreativeLinkDataFields::CHILD_ATTACHMENTS])) {
                //carousel ads
                foreach ($creative[AdCreativeFields::OBJECT_STORY_SPEC][AdCreativeObjectStorySpecFields::LINK_DATA][AdCreativeLinkDataFields::CHILD_ATTACHMENTS] as $child) {
                    if (isset($child[AdCreativeFields::IMAGE_HASH]) && $image = $this->findImageByHash($child[AdCreativeFields::IMAGE_HASH], $images)) {
                        $this->storeAds($mediaAccountId, $creativeMap[$creative[AdCreativeFields::ID]], $image, self::OBJECT_TYPE_IMAGE);
                    }
                }

            } elseif (isset($creative[AdCreativeFields::IMAGE_HASH])) {
                if ($image = $this->findImageByHash($creative[AdCreativeFields::IMAGE_HASH], $images)) {
                    $this->storeAds($mediaAccountId, $creativeMap[$creative[AdCreativeFields::ID]], $image, self::OBJECT_TYPE_IMAGE);
                }
            }
        }
    }

    /**
     * @param $hash
     * @param $images
     * @return null
     */
    private function findImageByHash($hash, $images)
    {
        //検索頻度が高いのでクエリを使わない
        foreach ($images as $image) {
            if ($image->hash_code == $hash) {
                return $image;
            }
        }
        return null;
    }

    /**
     * @param $fbId
     * @param $slideshows
     * @return null
     */
    private function findSlideshowByFBId($fbId, $slideshows)
    {
        foreach ($slideshows as $slideshow) {
            if ($slideshow->media_object_id == $fbId) {
                return $slideshow;
            }
        }
        return null;
    }

    /**
     * @param $mediaAccountId
     * @param $adIds
     * @param $object
     * @param int $objectType
     */
    public function storeAds($mediaAccountId, $adIds, $object, $objectType = self::OBJECT_TYPE_IMAGE)
    {
        foreach ($adIds as $adId) {
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
}
