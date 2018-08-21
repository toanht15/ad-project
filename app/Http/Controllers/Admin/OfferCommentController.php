<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\User;
use App\Service\OfferService;
use App\Service\AdvertiserService;
use App\Service\InstagramAccountService;
use Illuminate\Http\Request;
use Classes\PMAPIClient;

class OfferCommentController extends Controller
{
    /**
     * @param InstagramAccountService $instagramAccountService
     * @return \Illuminate\Contracts\View\View
     */
    public function index(InstagramAccountService $instagramAccountService)
    {
        $advertiserList = [];
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $advertisers = $advertiserService->all();

        foreach ($advertisers as &$advertiser) {
            $offeredOffer = $advertiser->countOfferByStatus(\App\Models\Offer::STATUS_OFFERED);
            $advertiser->offered_offer = $offeredOffer;
            $advertiser->commented_offer = $advertiser->countOfferByStatus(\App\Models\Offer::STATUS_COMMENTED);
            $instagramAccount = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id)->first();
            $advertiser->instagram_account_name = $instagramAccount ? $instagramAccount->name : '';
            $advertiserList[] = $advertiser;
        }
        return view()->make('admin.offer_comment_list', [
            'advertisers' => $advertiserList
        ]);
    }

    public function getAdvertiserOffer($advId)
    {
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        /** @var OfferService $offerService */
        $offerService = app(OfferService::class);
        /** @var InstagramAccountService $instagramAccountService */
        $instagramAccountService = app(InstagramAccountService::class);

        $advertiser = $advertiserService->findBy('id', $advId);
        if (empty($advertiser)) {
            abort(404);
        }
        $advertiserName = $advertiser->name;
        $instagramAccount = $instagramAccountService->getInstagramAccountByAdvertiserId($advId);
        $instagramAccountName = (count($instagramAccount) == 0) ? "" : $instagramAccount[0]->name;
        $offers = $offerService->getOfferGroupByMediaId($advId);
        $offers->load('offerSet');

        return view()->make('admin.advertiser_offer_list', [
            'offers' => $offers,
            'advertiserName' => $advertiserName,
            'instagramAccountName' => $instagramAccountName
        ]);
    }

    /**
     * @param Request $request
     * @param OfferService $offerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeAdvertiserOfferStatus(Request $request, OfferService $offerService)
    {
        /**
         * @var User $admin
         */
        $admin = \Auth::guard('admin')->user();

        $offerId = $request->input('offerId');
        $offerStatus = $request->input('offerStatus');

        try {
            $offerService->updateStatusOffer($offerId, $offerStatus);
            \Log::info("[EGC Comment] EGC ID $admin->id: $admin->user_name change status to $offerStatus on offer id $offerId");
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(400);
        }
        return response()->json(200);
    }
}
