<?php

namespace App\Console\Commands;

use App\Models\ApprovedNotification;
use App\Models\Offer;
use App\Models\OfferSet;
use App\Models\OfferSetGroup;
use App\Models\Post;
use App\Service\UserService;
use Classes\PMAPIClient;
use Illuminate\Console\Command;

class SyncOfferStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncOfferStatus';


    public function handle()
    {
        $offerSets = Offer::join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
            ->whereIn('offers.status', [Offer::STATUS_OFFERED,Offer::STATUS_COMMENTED])
            ->groupBy('offer_sets.id')
            ->select(['offer_sets.id', 'offer_sets.pm_id', 'offer_sets.offer_set_group_id'])
            ->distinct()
            ->get();
        $pmClient = new PMAPIClient();
        foreach ($offerSets as $offerSet) {
            if (!$offerSet->pm_id) {
                continue;
            }
            try {
                $response = $pmClient->getOfferStatus($offerSet->pm_id);
                if (!isset($response['data'])) {
                    continue;
                }

                $this->doSyncStatus($response, $offerSet);
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }

    /**
     * @param $response
     * @param $offerSet
     */
    private function doSyncStatus($response, $offerSet)
    {
        $offerSetGroup = OfferSetGroup::find($offerSet->offer_set_group_id);
        $approvedCount = 0;
        $failedCount = 0;
        $offeredTimeSign = (new \DateTime())->format('Y-m-d H:i:s');
        foreach ($response['data'] as $pmData) {
            $postIds = Post::where(['post_id' => $pmData['post_id']])->pluck('id');
            $offers = Offer::whereIn('post_id', $postIds)->where(['offer_set_id' => $offerSet->id])->get();
            if (!count($offers)) {
                continue;
            }

            foreach ($offers as $offer) {
                if ($offer->status == Offer::STATUS_LIVING) {
                    continue;
                }

                if ($offer->status != Offer::STATUS_APPROVED && $pmData['permission_status'] == Offer::STATUS_APPROVED) {
                    $approvedCount ++;
                    $offeredTimeSign = $pmData['approved_at'] < $offeredTimeSign ? $pmData['approved_at'] : $offeredTimeSign;
                    $offer->approved_at = $pmData['approved_at'];
                } elseif ($offer->status != Offer::STATUS_COMMENT_FALSE && $pmData['permission_status'] == Offer::STATUS_COMMENT_FALSE) {
                    $failedCount ++;
                }

                $offer->status      = $pmData['permission_status'];
                $offer->save();
            }
        }

        $offerSetGroup->approved_image_count += $approvedCount;
        $offerSetGroup->offering_image_count -= ($approvedCount + $failedCount);
        $offerSetGroup->save();

        $this->createNotification($offerSetGroup, $approvedCount, $offeredTimeSign);
    }

    /**
     * @param $offerSetGroup
     * @param int $approvedCount
     * @param $offeredTimeSign
     */
    public function createNotification($offerSetGroup, $approvedCount = 0, $offeredTimeSign)
    {
        if (!$approvedCount) {
            return;
        }
        $unreadNotification = ApprovedNotification::where([
            'offer_set_group_id' => $offerSetGroup->id,
            'is_read' => false
        ])->get();

        $excludeUserIds = [];
        if ($unreadNotification->count()) {
            foreach ($unreadNotification as $notification) {
                $excludeUserIds[] = $notification->user_id;
                $notification->new_approve_count += $approvedCount;
                $notification->save();
            }
        }
        /** @var UserService $userService */
        $userService = app(UserService::class);
        $users = $userService->getUserByAdvertiserId($offerSetGroup->advertiser_id);

        foreach ($users as $user) {
            if (in_array($user->id, $excludeUserIds)) {
                continue;
            }
            $notification = new ApprovedNotification();
            $notification->user_id = $user->id;
            $notification->advertiser_id = $offerSetGroup->advertiser_id;
            $notification->offer_set_group_id = $offerSetGroup->id;
            $notification->new_approve_count = $approvedCount;
            $notification->offered_time_sign = $offeredTimeSign;
            $notification->save();
        }
    }
}
