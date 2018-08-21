<?php

namespace App\Console\Commands;

use App\Models\ContractService;
use App\Service\AdvertiserService;
use App\Service\MediaAdAccountInsightService;
use App\Service\MediaAccountService;
use Classes\Constants;
use Classes\FacebookGraphClient;
use FacebookAds\Object\Fields\AdsInsightsFields;

class GetDailyFbMediaAccountInsight extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getDailyFbMediaAccountInsight {--period=1}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $period = $this->option('period');
        $since = (new \DateTime('-'. $period. 'months'))->format('Y-m-d');
        $now = (new \DateTime())->format('Y-m-d');

        /** @var MediaAdAccountInsightService $mediaAdAccountInsightService */
        $mediaAdAccountInsightService = app(MediaAdAccountInsightService::class);
        /** @var MediaAccountService $mediaAccountService */
        $mediaAccountService = app(MediaAccountService::class);
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken();
        $facebookClient = new FacebookGraphClient();
        foreach ($mediaAccounts as $mediaAccount) {
            if ($mediaAccount->media_type != Constants::MEDIA_FACEBOOK || $mediaAccount->token_expired_flg || !$advertiserService->hasActiveContract($mediaAccount->advertiser_id, ContractService::FOR_AD)) {
                continue;
            }
            try {
                $isUsableToken = $facebookClient->setAccessTokenByMediaAccount($mediaAccount);
                if (!$isUsableToken) {
                    continue;
                }
                $params = [
                    'time_increment' => 1,
                    'time_range'    => [
                        'since' => $since,
                        'until' => $now
                    ],
                    'fields'    => implode(',', [
                        AdsInsightsFields::SPEND
                    ])
                ];

                $response = $facebookClient->getInsights('act_'.$mediaAccount->media_account_id, $params);

                foreach ($response as $data) {
                    $identify = [
                        'media_account_id' => $mediaAccount->id,
                        'date'          => $data[AdsInsightsFields::DATE_START]
                    ];
                    $mediaAdAccountInsightService->createOrUpdate($identify, $identify, [
                        'spend' => $data[AdsInsightsFields::SPEND]
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }
}
