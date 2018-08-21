<?php

namespace App\Http\Controllers\Admin;

use App\Models\InstagramAccount;
use App\UGCConfig;
use Classes\Constants;
use Classes\FacebookGraphClient;
use Classes\InstagramApiClient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExtendFunctionController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function extendFunctionPage()
    {
        return view()->make('admin.extend_function');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createDynamicCreative(Request $request)
    {
        $adAccountId = $request->get('ad_account_id');
        $adSetId = $request->get('ad_set_id');
        $linkUrls = $request->get('link_urls');
        $adFormat = $request->get('ad_formats');
        $adName = $request->get('ad_name');
        $pageId = $request->get('page_id');
        $instagramActorId = $request->get('instagram_actor_id');
        $callToActionType = $request->get('call_to_action_types');
        $imageHashs = split_string_by_new_line($request->get('image_hashs'));
        $videoIds = $request->get('video_ids');
        if (!$videoIds) {
            $videoIds = [];
        }
        $bodies = explode('---', $request->get('bodies'));
        $titles = explode('---', $request->get('titles'));
        $descriptions = explode('---', $request->get('descriptions'));

        $admin = \Auth::guard('admin')->user();
        $fbClient = new FacebookGraphClient($admin->getAccessToken(Constants::MEDIA_FACEBOOK));

        $assetFeed = $fbClient->createAssetFeeds($adAccountId, $imageHashs, $videoIds, $bodies, $titles, $descriptions, $adFormat, $linkUrls, $callToActionType);
        if (isset($assetFeed['error']['message'])) {
            $request->session()->flash(Constants::ERROR_MESSAGE, $assetFeed['error']['message']);
            return back()->withInput($request->input());
        }

        $creative = $fbClient->createDynamicCreative($adAccountId, $assetFeed['id'], $pageId, $instagramActorId);
        if (isset($creative['error']['message'])) {
            $request->session()->flash(Constants::ERROR_MESSAGE, $creative['error']['message']);
            return back()->withInput($request->input());
        }

        $ad = $fbClient->createDynamicCreativeAd($adAccountId, $adSetId, $creative['id'], $adName);
        if (isset($ad['error']['message'])) {
            $request->session()->flash(Constants::ERROR_MESSAGE, $ad['error']['message']);
            return back()->withInput($request->input());
        }

        $request->session()->flash(Constants::INFO_MESSAGE, 'Dynamic Creative Ad has been created, ad ID: '.$ad['id']);

        return back()->withInput($request->input());
    }

    /**
     * @param Request $request
     * @param $adAccountId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAdVideos(Request $request, $adAccountId)
    {
        $admin = \Auth::guard('admin')->user();
        $fbClient = new FacebookGraphClient($admin->getAccessToken(Constants::MEDIA_FACEBOOK));
        $adVideos = $fbClient->getVideos($adAccountId, 100);

        return response()->json($adVideos['data']);
    }

    /**
     * @param Request $request
     * @param $adAccountId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetInstagramActors(Request $request, $adAccountId)
    {
        $admin = \Auth::guard('admin')->user();
        $fbClient = new FacebookGraphClient($admin->getAccessToken(Constants::MEDIA_FACEBOOK));
        $actors = $fbClient->getInstagramAccount($adAccountId);

        return response()->json($actors['data']);
    }
}
