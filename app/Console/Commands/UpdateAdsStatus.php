<?php

namespace App\Console\Commands;

use App\Models\MediaAd;
use App\Repositories\Eloquent\MediaAdsInsightRepository;

class UpdateAdsStatus extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateAdsStatus {--stoppedAd}';

    public function doCommand()
    {

        $updateStoppedAds = $this->option('stoppedAd');
        $since = (new \DateTime('-30 days'))->format('Y-m-d');
        $until = (new \DateTime())->format('Y-m-d');
        if ($updateStoppedAds) {
            // if stopped ad has spend in 1 month, change status to running
            $this->updateStoppedAds($since, $until);
            return;
        }

        // if ad has not spend in 1 month, change status to stopped
        $this->updateActiveAds($since, $until);
    }

    public function updateActiveAds($since, $until)
    {
        /** @var MediaAdsInsightRepository $mediaAdsInsightRepository */
        $mediaAdsInsightRepository = app(MediaAdsInsightRepository::class);
        $adIds = $mediaAdsInsightRepository->getHasSpendAdIdsWithStatus(MediaAd::STATUS_RUNNING, $since, $until);

        MediaAd::whereNotIn('id', $adIds)
            ->where(['status' => MediaAd::STATUS_RUNNING])
            ->update(['status' => MediaAd::STATUS_STOPPED]);
    }

    public function updateStoppedAds($since, $until)
    {
        /** @var MediaAdsInsightRepository $mediaAdsInsightRepository */
        $mediaAdsInsightRepository = app(MediaAdsInsightRepository::class);
        $adIds = $mediaAdsInsightRepository->getHasSpendAdIdsWithStatus(MediaAd::STATUS_STOPPED, $since, $until);

        MediaAd::whereIn('id', $adIds)
            ->where(['status' => MediaAd::STATUS_STOPPED])
            ->update(['status' => MediaAd::STATUS_RUNNING]);
    }
}
