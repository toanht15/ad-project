<?php


namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Slideshow;
use App\Service\MediaAccountService;
use App\Service\SlideshowService;
use Classes\Constants;
use Classes\Roles;
use Illuminate\Http\Request;

class SlideshowController extends Controller
{

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @return \Illuminate\Contracts\View\View
     */
    public function listPage(Request $request, SlideshowService $slideshowService)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $slideshows = $slideshowService->getList($advertiser->id);

        return view()->make('slideshow.list', [
            'slideshows' => $slideshows,
            'advertiser'  => $advertiser
        ]);
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param $slideshowId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, SlideshowService $slideshowService, $slideshowId = null)
    {
        $this->validate($request, [
            'image_ids' => 'required|array',
            'time_per_img'  => 'required|integer'
        ]);

        $fixFlg = (int)$request->input('is_fix');
        $imgIds = $request->input('image_ids');
        $timePerImg = $request->input('time_per_img');
        $videoType = $request->input('video_type');
        $effectType = (int)$request->input('effect_type');

        $advertiser = \Auth::guard('advertiser')->user();

        $idstr = implode(',', array_unique($imgIds));
        $images = Image::selectRaw('id, image_url')
            ->where('images.advertiser_id', $advertiser->id)
            ->whereIn('id', $imgIds)
            ->orderByRaw('FIELD(images.id, '.$idstr.')')
            ->get();

        $slideshow = $slideshowService->createOrUpdateSlideshow($advertiser, $slideshowId, $images, $timePerImg, $effectType, $fixFlg, $videoType);

        if (!$slideshow) {
            $response = [
                'errors' => [
                    Constants::TOASTR_ERROR => 'スライドショーが作成できませんでした'
                ]
            ];
            $code = 400;
        } else {
            $response = [
                'url'   => SlideshowService::getVideoUrl($slideshow->name, $advertiser->id),
                'size'  => SlideshowService::changeVideoSizeFormat($slideshow->size)
            ];
            $code = 200;
        }

        return response()->json($response, $code);
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param MediaAccountService $mediaAccountService
     * @param $slideshowId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadToMediaAccount(Request $request, SlideshowService $slideshowService, MediaAccountService $mediaAccountService, $slideshowId)
    {
        $this->validate($request, [
            'media_account_id' => 'required'
        ]);
        $mediaAccountId = $request->get('media_account_id');
        $advertiser = \Auth::guard('advertiser')->user();
        $mediaAccount = $mediaAccountService->findWithToken('id', $mediaAccountId)->first();
        if (!$advertiser->can(Roles::PERMISSION_UPDATE, $mediaAccount)) {
            abort(404);
        }
        $slideshow = $slideshowService->findModel($slideshowId);

        try {
            if ($mediaAccount->media_type == Constants::MEDIA_TWITTER) {
                $tweet = $request->get('tweet');
                $creativeType = $request->get('creative_type');
                $slideshowService->uploadToTwitter($mediaAccount, $slideshow, $tweet, $creativeType);
            } else {
                $slideshowService->uploadToFacebook($mediaAccount, $slideshow);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, 'アップロードに失敗しました');

            return back();
        }

        $request->session()->flash(Constants::INFO_MESSAGE, 'スライドショーをアップロードしました');

        return back();
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param $slideshowId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request, SlideshowService $slideshowService, $slideshowId)
    {
        $slideshowService->deleteSlideshow($slideshowId, \Auth::guard('advertiser')->user());

        return back();
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param $serialNo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete_preview(Request $request, SlideshowService $slideshowService, $serialNo)
    {
        $slideshowService->deletePreview($serialNo, \Auth::guard('advertiser')->user());
        
        return back();
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param MediaAccountService $mediaAccountService
     * @param $slideshowId
     * @return \Illuminate\Contracts\View\View
     */
    public function detail(Request $request, SlideshowService $slideshowService, MediaAccountService $mediaAccountService, $slideshowId)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $slideshow = $slideshowService->findModel($slideshowId);
        $images = $slideshowService->getImageOfSlideshow($slideshowId);
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken($advertiser->id);

        return view()->make('slideshow.detail', [
            'slideshow' => $slideshow,
            'adAccount' => $advertiser,
            'mediaAccounts' => $mediaAccounts,
            'imageCount' => $images->count(),
            'firstImage' => $images->first()
        ]);
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param null $slideshowId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetSlideshowData(Request $request, SlideshowService $slideshowService, $slideshowId)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $slideshowData = $slideshowService->getSlideshowWithImages($slideshowId, $advertiser);

        return response()->json($slideshowData);
    }

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param $slideshowId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetKpi(Request $request, SlideshowService $slideshowService, $slideshowId)
    {
        $result = $slideshowService->getSlideshowTotalKpi($slideshowId);

        return response()->json($result->toArray(), 200, [], JSON_NUMERIC_CHECK);
    }
}
