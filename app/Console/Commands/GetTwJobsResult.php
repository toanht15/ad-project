<?php

namespace App\Console\Commands;

use App\Service\AdConversionService;
use App\Service\ConversionTypeService;
use App\Service\MediaAdAccountInsightService;
use App\Service\MediaAdsInsightService;
use App\Service\MediaAccountService;
use App\Service\MediaAdService;
use App\Service\OfferService;
use App\Service\TwitterStatJobResultService;
use App\Service\TwitterStatJobService;
use Classes\Constants;
use Classes\TwitterApiClient;
use Hborras\TwitterAdsSDK\TwitterAds\Analytics;
use Hborras\TwitterAdsSDK\TwitterAds\Fields\AnalyticsFields;
use Hborras\TwitterAdsSDK\TwitterAdsException;

class GetTwJobsResult extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getTwJobsResult';

    /** @var TwitterApiClient  */
    protected $twClient;
    /** @var MediaAdAccountInsightService */
    protected $fbAdAccountInsightService;
    /** @var MediaAdService */
    protected $mediaAdService;
    /** @var MediaAdsInsightService */
    protected $mediaAdInsightService;
    /** @var  ConversionTypeService */
    protected $conversionTypeService;
    /** @var  AdConversionService */
    protected $adConversionService;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        /** @var MediaAccountService */
        $mediaAccountService = app(MediaAccountService::class);
        /** @var TwitterStatJobService */
        $twitterStatJobService = app(TwitterStatJobService::class);
        /** @var TwitterStatJobResultService */
        $twitterStatJobResultService = app(TwitterStatJobResultService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken();
        $this->twClient = TwitterApiClient::createInstance();
        $this->fbAdAccountInsightService = app(MediaAdAccountInsightService::class);
        $this->mediaAdService = app(MediaAdService::class);
        $this->mediaAdInsightService = app(MediaAdsInsightService::class);
        $this->conversionTypeService = app(ConversionTypeService::class);
        $this->adConversionService = app(AdConversionService::class);

        foreach ($mediaAccounts as $mediaAccount) {
            if ($mediaAccount->media_type != Constants::MEDIA_TWITTER || $mediaAccount->token_expired_flg) {
                continue;
            }
            try {
                $jobs = $twitterStatJobService->getWhere([
                    'media_account_id' => $mediaAccount->id,
                    'status' => 'PROCESSING'
                ]);
                if (!$jobs->count()) {
                    continue;
                }
                $this->twClient->setOauthToken($mediaAccount->access_token, $mediaAccount->refresh_token);
                $this->twClient->setAccountId($mediaAccount->media_account_id);
                $jobIds = $jobs->pluck('job_id')->all();
                // support to 200 jobs
                $jobResponses = $this->twClient->getJobs(implode(',',$jobIds));

                foreach ($jobResponses as $jobResponse) {
                    if ($jobResponse->getStatus() != 'SUCCESS') {
                        // update status if job is failed
                        if ($jobResponse->getStatus() != 'PROCESSING') {
                            $twitterStatJobService->updateJobFromResponse($jobResponse);
                        }
                        continue;
                    }
                    try {
                        $data = $this->getStatsData($jobResponse->getUrl());

                        \DB::beginTransaction();

                        $twitterStatJobService->updateJobFromResponse($jobResponse);

                        if ($jobResponse->getEntity() == AnalyticsFields::FUNDING_INSTRUMENT) {
                            // ad account stat job
                            $this->storeAdAccountStat($data, $jobResponse, $mediaAccount->id);
                        } else {
                            $interval = new \DateInterval('P1D');
                            $dateRange = new \DatePeriod($jobResponse->getStartTime()->modify('+1 day'), $interval, $jobResponse->getEndTime()->modify('+1 day'));
                            // promoted tweet stat job
                            $this->storePromotedTweetStat($data, $dateRange, $jobResponse, $mediaAccount->id);
                            // save result for backup
                            $job = $twitterStatJobService->findBy('job_id', $jobResponse->getIdStr(), ['id']);
                            $twitterStatJobResultService->createModel([
                                'job_id' => $job->id,
                                'result' => json_encode($data)
                            ]);
                        }

                        \DB::commit();

                    } catch (\Exception $e) {
                        \DB::rollBack();
                        \Log::error($e);
                    }
                }
                // update offer ctr
                $offerService->updateOfferCTR($mediaAccount);

            } catch (TwitterAdsException $e) {
                \Log::error($e->getErrors());
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }

    /**
     * @param $url
     * @return array
     */
    public function getStatsData($url) {
        $result = json_decode($this->unZip($url), true);

        return isset($result['data']) ? $result['data'] : [];
    }

    /**
     * @param $url
     * @return string
     */
    public function unZip($url) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return gzdecode($response);
    }

    /**
     * @param $datas
     * @param Analytics\Job $jobResponse
     * @param $mediaAccountId
     */
    public function storeAdAccountStat($datas, Analytics\Job $jobResponse, $mediaAccountId)
    {
        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod($jobResponse->getStartTime()->modify('+1 day'), $interval, $jobResponse->getEndTime()->modify('+1 day'));
        $statDatas = [];
        foreach ($datas as $data) {
            if (!isset($data['id_data']['0']['metrics']['billed_charge_local_micro']) || !$data['id_data']['0']['metrics']['billed_charge_local_micro']) {
                // empty data
                continue;
            }
            $billedData = $data['id_data']['0']['metrics']['billed_charge_local_micro'];
            if (count($billedData) != iterator_count($dateRange)) {
                \Log::error('billedData and dateRange is not match jobId:'.$jobResponse->getIdStr());
                return;
            }
            foreach($dateRange as $index => $date) {
                if (!isset($statDatas[$index])) {
                    $statDatas[$index] = ['spend' => 0];
                }
                $statDatas[$index]['date'] = $date->format('Y-m-d');
                $statDatas[$index]['spend'] += round($billedData[$index]/1000000);
                $statDatas[$index]['media_account_id'] = $mediaAccountId;
            }
        }

        foreach ($statDatas as $statData) {
            // store insight data
            $identify = $statData;
            unset($identify['spend']);
            $this->fbAdAccountInsightService->createOrUpdate($identify, $identify, [
                'spend' => $statData['spend']
            ]);
        }
    }

    /**
     * @param $datas
     * @param $dateRange
     * @param Analytics\Job $jobResponse
     * @param $mediaAccountId
     */
    public function storePromotedTweetStat($datas, $dateRange, Analytics\Job $jobResponse, $mediaAccountId)
    {
        foreach ($datas as $data) {
            if (!isset($data['id_data']['0']['metrics']['billed_charge_local_micro']) || !$data['id_data']['0']['metrics']['billed_charge_local_micro']) {
                // empty data
                continue;
            }
            $billedData = $data['id_data']['0']['metrics']['billed_charge_local_micro'];
            $dateCount = iterator_count($dateRange);
            if (count($billedData) != $dateCount) {
                \Log::error('billedData and dateRange is not match jobId:'.$jobResponse->getIdStr());
                return;
            }
            $metrics = $data['id_data']['0']['metrics'];
            $fbAd = $this->mediaAdService->findBy('ad_id', $data['id']);
            if (!$fbAd) {
                return;
            }
            foreach($dateRange as $index => $date) {
                $ident = [
                    'date' => $date->format('Y-m-d'),
                    'facebook_ad_id' => $fbAd->id,
                    'media_account_id' => $mediaAccountId
                ];

                $updateColumn = [
                    'spend' => round($metrics['billed_charge_local_micro'][$index]/1000000),
                    'click' => $metrics['clicks'][$index],
                    'impression' => $metrics['impressions'][$index],
                    'ctr' => $metrics['impressions'][$index] ? $metrics['clicks'][$index] * 100 / $metrics['impressions'][$index] : 0
                ];

                $insight = $this->mediaAdInsightService->createOrUpdate($ident, $ident, $updateColumn);
                $this->storeTwitterConversion($data['id_data']['0']['metrics'], $insight, $index, $dateCount);
            }
        }
    }

    /**
     * @param $metrics
     * @param $insight
     * @param $index
     * @param $dateCount
     */
    public function storeTwitterConversion($metrics, $insight, $index, $dateCount)
    {
        foreach ($metrics as $key => $metric) {
            if (!$metric || ((isset($metric['metric']) && !$metric['metric'])) || !isset($metric['metric'])) {
                // empty data
                continue;
            }
            if (isset($metric['metric']) && $metric['metric']) {
                $metric = $metric['metric'];
            }
            if (count($metric) != $dateCount) {
                \Log::error('Metric and dateRange is not match insightId:'.$insight->id.' '.$key);
                continue;
            }
            // store conversion
            if (!$metric[$index]) {
                continue;
            }
            // check and store conversion type
            $conversionType = $this->conversionTypeService->createOrUpdate([
                'action_type' => $key
            ], [
                'action_type' => $key,
                'label' => $key
            ]);

            $this->adConversionService->createOrUpdate([
                'facebook_ads_insight_id' => $insight->id,
                'facebook_action_id' => $conversionType->id
            ], [
                'media_account_id' => $insight->media_account_id,
                'facebook_ad_id' => $insight->facebook_ad_id,
                'facebook_ads_insight_id' => $insight->id,
                'facebook_action_id' => $conversionType->id,
                'date' => $insight->date
            ], [
                'value' => $metric[$index]
            ]);
        }
    }
}
