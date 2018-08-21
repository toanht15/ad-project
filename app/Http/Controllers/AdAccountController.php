<?php

namespace App\Http\Controllers;


use App\Models\Hashtag;
use App\Models\SearchCondition;
use App\Models\SearchHashtag;
use App\Models\Offer;
use App\Service\AdvertiserService;

class AdAccountController extends Controller
{

    /**
     * @param AdvertiserService $advertiserService
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeTutorial(AdvertiserService $advertiserService)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $advertiserService->updateModel(['completed_tutorial_flg' => true], $advertiser->id);

        return response()->json('OK');
    }
}
