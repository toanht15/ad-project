<?php

namespace App\Console\Commands;

use App\Models\Advertiser;
use App\Models\Offer;
use App\Models\OfferSetGroup;
use App\Service\AdvertiserService;
use App\Service\OfferService;
use Illuminate\Console\Command;

class OneTimeUpdateOfferLivingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'OneTimeUpdateOfferLivingStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->detectLivingOffer();
    }

    public function detectLivingOffer()
    {
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);
        $advertisers = $advertiserService->all();
        //LIVINGと言う新しいオファーステータスがあった。
        foreach ($advertisers as $advertiser) {
            $offers = $offerService->getOfferCTRByAdvertiserIdAndDate($advertiser->id);

            foreach ($offers as $offer) {
                if ($offer->spend > 0 && $offer->status != Offer::STATUS_LIVING) {
                    $offer->status = Offer::STATUS_LIVING;
                }
                $offer->save();
            }
        }
    }

    public function countOfferByStatus()
    {
        //他のステータスを整理する
        $offerSetGroups = OfferSetGroup::all();
        foreach ($offerSetGroups as $offerSetGroup) {
            $statistics = Offer::join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
                ->where('offer_sets.offer_set_group_id', $offerSetGroup->id)
                ->groupBy('offers.status')
                ->selectRaw('offers.status, count(distinct offers.id) as ugc_count')
                ->get();

            $approvedCount = 0;
            $offeringCount = 0;
            $failedCount = 0;
            foreach ($statistics as $statistic) {
                if ($statistic->status === Offer::STATUS_OFFERED || $statistic->status == Offer::STATUS_COMMENTED) {
                    $offeringCount += $statistic->ugc_count;
                } elseif ($statistic->status == Offer::STATUS_APPROVED || $statistic->status == Offer::STATUS_LIVING) {
                    $approvedCount += $statistic->ugc_count;
                } elseif ($statistic->status == Offer::STATUS_COMMENT_FALSE) {
                    $failedCount = $statistic->ugc_count;
                }
            }
            $offerSetGroup->approved_image_count = $approvedCount;
            $offerSetGroup->offering_image_count = $offeringCount;
            $offerSetGroup->failed_image_count = $failedCount;

            $offerSetGroup->save();
        }
    }
}
