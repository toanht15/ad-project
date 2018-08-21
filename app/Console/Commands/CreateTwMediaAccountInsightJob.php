<?php

namespace App\Console\Commands;

use App\Service\MediaAccountService;
use App\Service\TwitterStatJobService;
use Classes\Constants;
use Classes\TwitterApiClient;
use Hborras\TwitterAdsSDK\TwitterAds\Enumerations;
use Hborras\TwitterAdsSDK\TwitterAds\Fields\AnalyticsFields;
use Hborras\TwitterAdsSDK\TwitterAdsException;

class CreateTwMediaAccountInsightJob extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createTwMediaAccountInsightJob {--period=1}';

    /** @var TwitterApiClient  */
    protected $twClient;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $period = $this->option('period');
        $since = (new \DateTime('-'. $period. 'months'))->setTime(0, 0, 0);
        $now = (new \DateTime())->setTime(0, 0, 0);

        /** @var MediaAccountService $mediaAccountService */
        $mediaAccountService = app(MediaAccountService::class);
        /** @var TwitterStatJobService $twitterStatJobService */
        $twitterStatJobService = app(TwitterStatJobService::class);
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken();
        $this->twClient = TwitterApiClient::createInstance();

        foreach ($mediaAccounts as $mediaAccount) {
            if ($mediaAccount->media_type != Constants::MEDIA_TWITTER || $mediaAccount->token_expired_flg) {
                continue;
            }
            try {
                $this->twClient->setOauthToken($mediaAccount->access_token, $mediaAccount->refresh_token);
                $this->twClient->setAccountId($mediaAccount->media_account_id);

                $params = [
                    AnalyticsFields::START_TIME => $since,
                    AnalyticsFields::END_TIME => $now,
                    AnalyticsFields::GRANULARITY => Enumerations::GRANULARITY_DAY,
                    AnalyticsFields::ENTITY => AnalyticsFields::FUNDING_INSTRUMENT
                ];
                $metricGroup = [
                    AnalyticsFields::METRIC_GROUPS_BILLING
                ];

                $fundingInstruments = $this->twClient->getFundingInstrument();
                $fundingInstrumentIds = [];
                foreach ($fundingInstruments as $fundingInstrument) {
                    $fundingInstrumentIds[] = $fundingInstrument->getId();
                }
                // support to 20 funding instrument
                $job = $this->twClient->getAccount($mediaAccount->media_account_id)->all_stats($fundingInstrumentIds, $metricGroup, $params, true);
                $twitterStatJobService->createJobFromResponse($job, $mediaAccount->id);

            } catch (TwitterAdsException $e) {
                \Log::error($e->getErrors());
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }
}
