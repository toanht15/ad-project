<?php

namespace App\Console\Commands;

use App\Models\ContractService;
use App\Models\MediaAd;
use App\Repositories\Eloquent\MediaAdRepository;
use App\Service\AdConversionService;
use App\Service\AdvertiserService;
use App\Service\ConversionTypeService;
use App\Service\MediaAdsInsightService;
use App\Service\MediaAccountService;
use App\Service\MediaAdService;
use App\Service\OfferService;
use Classes\Constants;
use Classes\FacebookGraphClient;
use FacebookAds\Object\Fields\AdsInsightsFields;
use Helpers\SlackNotification;

class GetDailyFbAdsInsight extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getDailyFbAdsInsight {--stoppedAd}';
    /** @var  FacebookGraphClient $fbClient */
    protected $fbClient;
    /** @var OfferService  */
    protected $offerService;
    /** @var mediaAdInsightService */
    protected $mediaAdInsightService;
    /** @var ConversionTypeService */
    protected $conversionTypeService;
    /** @var AdConversionService */
    protected $adConversionService;

    public function doCommand()
    {
        $this->offerService = app(OfferService::class);
        $this->fbClient = new FacebookGraphClient();
        $this->mediaAdInsightService = app(MediaAdsInsightService::class);
        $this->conversionTypeService = app(ConversionTypeService::class);
        $this->adConversionService = app(AdConversionService::class);
        /** @var MediaAdService $mediaAdService */
        $mediaAdService = app(MediaAdService::class);
        /** @var MediaAccountService $mediaAccountService */
        $mediaAccountService = app(MediaAccountService::class);
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken();
        $getStoppedAdsInsight = $this->option('stoppedAd');
        foreach ($mediaAccounts as $mediaAccount) {
            if ($mediaAccount->media_type != Constants::MEDIA_FACEBOOK || $mediaAccount->token_expired_flg || !$advertiserService->hasActiveContract($mediaAccount->advertiser_id, ContractService::FOR_AD)) {
                continue;
            }
            try {
                if ($getStoppedAdsInsight) {
                    // get stopped ad which created from 1 month ago
                    $since = (new \DateTime('-30 days'))->format('Y-m-d');
                    $until = (new \DateTime(''))->format('Y-m-d');
                    /** @var MediaAdRepository $mediaAdRepository */
                    $mediaAdRepository = app(MediaAdRepository::class);
                    $ads = $mediaAdRepository->getStoppedAds($mediaAccount->id, $since, $until);
                } else {
                    $ads = $mediaAdService->getWhere([
                        'media_account_id' => $mediaAccount->id,
                        'status' => MediaAd::STATUS_RUNNING
                    ]);
                }

                if (!$ads->count()) {
                    continue;
                }

                $ids = [];
                $adMap = [];
                foreach ($ads as $ad) {
                    $ids[]              = $ad->ad_id;
                    // map media ad id with database ad id
                    // after getting insight from api, get database ad id from matched media ad id to save into database
                    $adMap[$ad->ad_id]  = $ad->id;
                }

                $isUsableToken = $this->fbClient->setAccessTokenByMediaAccount($mediaAccount);
                if (!$isUsableToken) {
                    continue;
                }

                $this->getAdsInsight($ids, $mediaAccount->id, $adMap);

                $this->offerService->updateOfferCTR($mediaAccount);

                $this->setLabelForCustomConversions($mediaAccount);

            } catch (\Exception $e) {
                \Log::error('AdAccount: '.$mediaAccount->name);
                \Log::error($e);
            }
        }
    }

    /**
     * @param $adIds
     * @param $mediaAccountId
     * @param $adMap
     */
    public function getAdsInsight($adIds, $mediaAccountId, $adMap)
    {
        $params = [
            'time_increment' => 1,
            'time_range'    => [
                'since' => (new \DateTime('-30 days'))->format('Y-m-d'),
                'until' => (new \DateTime())->format('Y-m-d')
            ],
            'fields'    => implode(',', [
                AdsInsightsFields::SPEND,
                AdsInsightsFields::IMPRESSIONS,
                AdsInsightsFields::CTR,
                AdsInsightsFields::AD_ID,
                AdsInsightsFields::ACTIONS
            ])
        ];
        $response = $this->fbClient->getInsights($adIds, $params);
        foreach ($response as $insight) {
            $ident = [
                'media_account_id' => $mediaAccountId,
                'facebook_ad_id' => $adMap[$insight[AdsInsightsFields::AD_ID]],
                'date' => $insight[AdsInsightsFields::DATE_START]
            ];
            $updateColumns = [
                'spend' => $insight[AdsInsightsFields::SPEND],
                'impression' => $insight[AdsInsightsFields::IMPRESSIONS],
                'ctr' => $insight[AdsInsightsFields::CTR],
                'click' => round($insight[AdsInsightsFields::CTR] * $insight[AdsInsightsFields::IMPRESSIONS] / 100)
            ];
            $adInsight = $this->mediaAdInsightService->createOrUpdate($ident, $ident, $updateColumns);

            if (!isset($insight[AdsInsightsFields::ACTIONS])) {
                continue;
            }
            //TODO create insert query
            foreach ($insight[AdsInsightsFields::ACTIONS] as $action) {
                $conversionTypeData = [
                    'action_type' => $action['action_type'],
                    'label' => $action['action_type']
                ];
                if (strpos($action['action_type'], 'offsite_conversion.custom') !== false) {
                    // custom conversion
                    unset($conversionTypeData['label']);
                }
                $cvType = $this->conversionTypeService->createOrUpdate([
                    'action_type' => $action['action_type']
                ], $conversionTypeData);

                $this->adConversionService->createOrUpdate([
                    'facebook_ads_insight_id' => $adInsight->id,
                    'facebook_action_id' => $cvType->id
                ], [
                    'media_account_id' => $adInsight->media_account_id,
                    'facebook_ad_id' => $adInsight->facebook_ad_id,
                    'facebook_ads_insight_id' => $adInsight->id,
                    'facebook_action_id' => $cvType->id,
                    'date' => $adInsight->date
                ], [
                    'value' => $action['value']
                ]);
            }
        }
    }

    /**
     * @param $mediaAccount
     */
    public function setLabelForCustomConversions($mediaAccount)
    {
        $unlabelCustomConversions = $this->conversionTypeService->getUnlabelCustomConversions($mediaAccount->id);

        if (!$unlabelCustomConversions->count()) {
            return;
        }

        $customConversions = $this->fbClient->getCustomConversions($mediaAccount->media_account_id);

        foreach ($unlabelCustomConversions as $unlabelCustomConversion) {
            foreach ($customConversions['data'] as $customConversion) {
                if (strpos($unlabelCustomConversion->action_type, $customConversion['id']) !== false) {
                    $this->conversionTypeService->updateModel([
                        'label' => $customConversion['name']
                    ], $unlabelCustomConversion->id);
                }
            }
        }
    }
}
