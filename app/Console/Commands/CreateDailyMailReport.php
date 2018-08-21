<?php

namespace App\Console\Commands;

use App\Models\Advertiser;
use App\Models\ContractService;
use App\Models\Offer;
use App\Service\AdvertiserService;
use App\Service\MediaAdsInsightService;
use App\Service\OfferService;
use App\Service\TenantService;
use App\Service\UserService;

class CreateDailyMailReport extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createDailyMailReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /** @var  TenantService */
    protected $tenantService;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $advertisers = Advertiser::all();
        /** @var MediaAdsInsightService $mediaAdsInsightService */
        $mediaAdsInsightService = app(MediaAdsInsightService::class);
        /** @var UserService $userService */
        $userService = app(UserService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $this->tenantService = app(TenantService::class);

        foreach ($advertisers as $advertiser) {
            try {
                $contract = $advertiserService->getActiveContract($advertiser->id, ContractService::FOR_AD);
                if (!$contract) {
                    continue;
                }
                $users = $userService->getUserByAdvertiserId($advertiser->id);

                if (!$users->count()) {
                    continue;
                }

                $yesterday = (new \DateTime('-1 day'))->format('Y-m-d');
                $kpi = $mediaAdsInsightService->getAdvertiserDailyKpi($advertiser->id, $yesterday, $yesterday);

                $upOffer = $offerService->getYesterdayUpOffers($advertiser->id, 1);
                $downOffer = $offerService->getYesterdayDownOffers($advertiser->id, 1);

                // TODO change to paginator (dont get max 1000 images)
                $newApproveds = $offerService->getApprovedByDate($advertiser->id, $yesterday, 1000, 'posts.like', 'desc');

                if ((!isset($kpi[$yesterday]['sum_spend']) || !$kpi[$yesterday]['sum_spend']) && $newApproveds->count() == 0) {
                    //メールを送信しない
                    continue;
                }

                foreach ($users as $user) {
                    if (!$user->email) {
                        continue;
                    }
                    $this->createDailyMailReport($yesterday, $user, $advertiser, isset($kpi[$yesterday]) ? $kpi[$yesterday] : [], $upOffer, $downOffer, $newApproveds);
                }
            } catch (\Exception $e) {
                \Log::error('CreateDailyMailReport error adAccountId:'.$advertiser->id);
                \Log::error($e);
            }
        }
    }

    /**
     * @param $yesterday
     * @param $user
     * @param $advertiser
     * @param $kpi
     * @param $upOffer
     * @param $downOffer
     * @param $newApproveds
     */
    public function createDailyMailReport($yesterday, $user, $advertiser, $kpi, $upOffer, $downOffer, $newApproveds)
    {
        $tenant = $this->tenantService->findModel($user->tenant_id);
        try {
            \Mail::send('emails.daily_report', [
                'tenant' => $tenant,
                'account' => $user,
                'date' => (new \DateTime($yesterday))->format('Y年m月d日'),
                'kpi' => $kpi,
                'adAccount' => $advertiser,
                'upOffers' => $upOffer,
                'downOffers' => $downOffer,
                'newApproveds' => $newApproveds
            ], function ($message) use ($user, $advertiser) {
                $message->from(env('MAIL_FROM'), env('MAIL_NAME'));
                $message->to($user->email)->subject('Letro Daily Report が届きました（'.$advertiser->name.'）-　'.(new \DateTime())->format('Y年m月d日'));
            });
        } catch (\Exception $e) {
            \Log::error('createDailyMailReport adAccountId:'. $advertiser->id);
            \Log::error($e);
        }
    }
}
