<?php

namespace App\Http\Controllers;

use App\Models\Advertiser;
use App\Models\ContractService;
use App\Models\Offer;
use App\Service\AdConversionService;
use App\Service\ConversionTypeService;
use App\Service\MediaAdsInsightService;
use App\Service\InstagramAccountService;
use App\Service\MediaAccountService;
use App\Service\OfferService;

use App\Service\SearchConditionService;
use \Auth;
use Classes\Constants;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    /**
     * @param Request $request
     * @param SearchConditionService $searchConditionService
     * @param InstagramAccountService $instagramAccountService
     * @param ConversionTypeService $conversionTypeService
     * @param MediaAccountService $mediaAccountService
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboard(Request $request, SearchConditionService $searchConditionService, InstagramAccountService $instagramAccountService,
                              ConversionTypeService $conversionTypeService, MediaAccountService $mediaAccountService)
    {
        /** @var Advertiser $advertiser */
        $advertiser = Auth::guard('advertiser')->user();


        $isFirstOwnerLogin = $advertiser->isFirstOwnerLogin();

        if ($isFirstOwnerLogin) {
            $site = \Session::get('site');
            $js_tag = $site->js_tag;
            if (!Auth::guard('admin')->check()) {
                $advertiser->setIsOwnedFirst();
            }
        } else {
            $js_tag = '';
        }


        list($dateStart, $dateStop) = get_request_datetime($request);

        $searchConditionCount = $searchConditionService->getWhere(['advertiser_id' => $advertiser->id], true);

        $instagramAccounts = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id);

        $conversionTypes = $conversionTypeService->getAvailableConversionType($advertiser->id);

        $mediaTypes = $mediaAccountService->getMediaAccountsWithToken($advertiser->id)->pluck('media_type')->all();
        $mediaTypes = array_unique($mediaTypes);


        return \View::make('dashboard.dashboard', [
                'searchConditionCount' => $searchConditionCount,
                'dateStart' => $dateStart,
                'dateStop' => $dateStop,
                'conversionTypes' => $conversionTypes,
                'hasInstagramAccounts' => $instagramAccounts->count() ? true : false,
                'mediaTypes' => $mediaTypes,
                'isFirstOwnerLogin' => $isFirstOwnerLogin,
                'js_tag' => $js_tag
            ]
        );
    }

    /**
     * @param Request $request
     * @param MediaAdsInsightService $mediaAdsInsightService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetGraphData(Request $request, MediaAdsInsightService $mediaAdsInsightService)
    {
        $advertiser = Auth::guard('advertiser')->user();
        list($dateStart, $dateStop) = get_request_datetime($request);
        $mediaTypes = explode(',', $request->get('media_type'));

        //data for daily report
        $totalData = $mediaAdsInsightService->getAdvertiserDailyKpi($advertiser->id, $dateStart, $dateStop);
        $interval = new \DateInterval('P1D');
        $dateRange = new \DatePeriod(new \DateTime($dateStart), $interval, new \DateTime($dateStop));
        foreach ($dateRange as $date) {
            if (!isset($totalData[$date->format('Y-m-d')])) {
                $totalData[$date->format('Y-m-d')] = [
                    'date' => $date->format('Y-m-d'),
                    'sum_click' => 0,
                    'sum_spend' => 0,
                    'sum_impression' => 0,
                    'sum_ctr' => 0
                ];
            }
        }
        ksort($totalData);
        $twData = [];
        $fbData = [];
        foreach ($mediaTypes as $mediaType) {
            if ($mediaType == Constants::MEDIA_FACEBOOK) {
                $fbData = $mediaAdsInsightService->getAdvertiserDailyKpi($advertiser->id, $dateStart, $dateStop, Constants::MEDIA_FACEBOOK);
            } elseif ($mediaType == Constants::MEDIA_TWITTER) {
                $twData = $mediaAdsInsightService->getAdvertiserDailyKpi($advertiser->id, $dateStart, $dateStop, Constants::MEDIA_TWITTER);
            }
        }

        return response()->json([
            'totalData' => $totalData,
            'fbData' => $fbData,
            'twData' => $twData
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * @param Request $request
     * @param OfferService $offerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetTopPerformanceUgc(Request $request, OfferService $offerService)
    {
        $advertiser = Auth::guard('advertiser')->user();
        list($dateStart, $dateStop) = get_request_datetime($request);
        $mediaTypes = explode(',', $request->get('media_type'));

        //TODO up limit to 20 and do slider js
        $limit = 4;
        if (count($mediaTypes) == 1) {
            // 選択された媒体のベストパフォーマンスUGCを検出
            $offerList = $offerService->getTopPerformanceUgc($advertiser->id, $mediaTypes[0], $dateStart, $dateStop, $limit);
        } else {
            // 全アカウントのベストパフォーマンスUGCを検出
            $offerList = $offerService->getTopPerformanceUgc($advertiser->id, null, $dateStart, $dateStop, $limit);
        }

        return response()->json($offerList->toArray(), 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * @param Request $request
     * @param OfferService $offerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetUgcStatus(Request $request, OfferService $offerService)
    {
        $advertiser = Auth::guard('advertiser')->user();
        $approved = $offerService->countOfferByAdvertiserId($advertiser->id, [Offer::STATUS_LIVING, Offer::STATUS_APPROVED]);
        $offered = $offerService->countOfferByAdvertiserId($advertiser->id, []);
        $spended = $offerService->countOfferByAdvertiserId($advertiser->id, [Offer::STATUS_LIVING]);

        $living = $offerService->countLivingOffer($advertiser->id);

        return response()->json([
            'approved' => $approved,
            'offered' => $offered,
            'spended' => $spended,
            'living' => $living
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * @param Request $request
     * @param AdConversionService $adConversionService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetTotalConversion(Request $request, AdConversionService $adConversionService)
    {
        $advertiser = Auth::guard('advertiser')->user();
        list($dateStart, $dateStop) = get_request_datetime($request);
        $cvType = $request->get('cv_type');
        if (!$cvType) {
            return response()->json(0);
        }
        $mediaTypes = explode(',', $request->get('media_type'));
        if (count($mediaTypes) == 1) {
            // 選択された媒体のコンバージョンのみを計測
            $conversion = $adConversionService->getDailyConversionByAdvertiserId($advertiser->id, $cvType, $dateStart, $dateStop, $mediaTypes[0])->sum('cv');
        } else {
            // 全アカウントのコンバージョンを計測
            $conversion = $adConversionService->getDailyConversionByAdvertiserId($advertiser->id, $cvType, $dateStart, $dateStop)->sum('cv');
        }

        return response()->json($conversion, 200, [], JSON_NUMERIC_CHECK);
    }
}
