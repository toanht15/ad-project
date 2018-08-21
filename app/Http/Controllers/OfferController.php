<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Post;
use App\Repositories\Eloquent\ImageRepository;
use App\Service\ArchivedPostService;
use App\Service\ImageService;
use App\Service\InstagramAccountService;
use App\Service\MediaAccountService;
use App\Service\OfferService;
use App\Repositories\Eloquent\OfferRepository;
use App\Service\OfferSetService;
use Classes\Roles;
use Illuminate\Http\Request;
use Classes\Constants;
use App\Models\Offer;
use App\Models\ArchivedPost;

class OfferController extends Controller
{
    /**
     * @param Request $request
     * @param OfferService $offerService
     * @param ArchivedPostService $archivedPostService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function archive(Request $request, OfferService $offerService, ArchivedPostService $archivedPostService)
    {
        $this->validate($request, [
            'offer_id' => 'required',
            'post_id' => 'required'
        ]);

        $offerId = $request->input('offer_id');
        $postId = $request->input('post_id');

        try {
            $offerService->archive($offerId, $postId, \Auth::guard('advertiser')->user()->id, \Auth::user()->id);

            $request->session()->flash(Constants::INFO_MESSAGE, 'リクエストを取り消しました');
        } catch (\Exception $exception) {
            $request->session()->flash(Constants::ERROR_MESSAGE, 'リクエストの取り消しに失敗しました');
            \Log::error($exception);
        }

        return back();
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param $offerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetEditedImgList(Request $request, ImageService $imageService, $offerId)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $imageList = $imageService->getImageListWithSummaryByOfferId($offerId, $advertiser->id);
        $originImage = $imageList->first();

        return response()->json([
            'imageList' => $imageList->toArray(),
            'originImage' => $originImage
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetEditedImgKpi(Request $request, ImageService $imageService, $imageId)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $image = $imageService->findModel($imageId);
        if (!$advertiser->can(Roles::PERMISSION_UPDATE, $image)) {
            return response()->json([], 400);
        }
        $kpi = $imageService->getImageKpi($imageId);

        return response()->json($kpi->toArray(), 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * @param OfferService $offerService
     * @param $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetOfferDetail(OfferService $offerService, $postId)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        /** @var OfferRepository $offerRepository */
        $offerRepository = app(OfferRepository::class);
        $offer = $offerRepository->queryWhere([
            'post_id' => $postId,
            'advertiser_id' => $advertiser->id
        ])->first();
        if ($offer) {
            $offerDetail = $offerService->getOfferWithReport($offer->id);
            return response()->json($offerDetail);
        }

        return response()->json("No offer");
    }

    /**
     * @param Request $request
     * @param OfferSetService $offerSetService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createOffers(Request $request, OfferSetService $offerSetService)
    {
        $this->validate($request, [
            'comment' => 'required|max:10000',
            'answer_tag' => 'required|max:255',
            'post_id' => 'required|array',
            'create_type' => 'required'
        ]);

        try {
            $user = \Auth::user();
            $advertiser = \Auth::guard('advertiser')->user();
            $comment = $request->input('comment');
            $answerHashtag = $request->input('answer_tag');
            $postIds = $request->input('post_id');
            $createType = $request->input('create_type');
            $useCommentTemplate = $request->input('no_comment_template') ? false : true;
            $offerSetService->createNewOfferSet($user, $advertiser, $postIds, $comment, $answerHashtag, $createType, $useCommentTemplate);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());
            return back();
        }

        $request->session()->flash(Constants::INFO_MESSAGE, 'リクエストしました');

        return back();
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param MediaAccountService $mediaAccountService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadFbMaterial(Request $request, ImageService $imageService, MediaAccountService $mediaAccountService)
    {
        $this->validate($request, [
            'image_id' => 'required',
            'media_account_id' => 'required'
        ]);
        $mediaAccountId = $request->get('media_account_id');
        $imageId = $request->get('image_id');
        $image = $imageService->findModel($imageId);
        $mediaAccount = $mediaAccountService->findWithToken('id', $mediaAccountId)->first();

        $advertiser = \Auth::guard('advertiser')->user();

        if (!$advertiser->can(Roles::PERMISSION_UPDATE, $image) || !$advertiser->can(Roles::PERMISSION_UPDATE, $mediaAccount)) {
            $request->session()->flash(Constants::ERROR_MESSAGE, '同期に失敗しました');
            return back();
        }

        try {
            $mediaAccountService->uploadFbMaterial($mediaAccount, $image);

        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());
            return back();
        }

        $request->session()->flash(Constants::INFO_MESSAGE, '同期しました');
        return back();
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param MediaAccountService $mediaAccountService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadTwMaterial(Request $request, ImageService $imageService, MediaAccountService $mediaAccountService)
    {
        $this->validate($request, [
            'image_id' => 'required',
            'media_account_id' => 'required',
            'creative_type' => 'required',
            'tweet' => 'required'
        ]);
        $mediaAccountId = $request->get('media_account_id');
        $imageId = $request->get('image_id');
        $creativeType = $request->get('creative_type');
        $tweet = $request->get('tweet');
        $image = $imageService->findModel($imageId);
        $mediaAccount = $mediaAccountService->findWithToken('id', $mediaAccountId)->first();

        $advertiser = \Auth::guard('advertiser')->user();

        if (!$advertiser->can(Roles::PERMISSION_UPDATE, $image) || !$advertiser->can(Roles::PERMISSION_UPDATE, $mediaAccount)) {
            $request->session()->flash(Constants::ERROR_MESSAGE, '同期に失敗しました');
            return back();
        }

        try {
            $mediaAccountService->uploadTwMaterial($mediaAccount, $image, $creativeType, $tweet);

        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());
            return back();
        }

        $request->session()->flash(Constants::INFO_MESSAGE, '同期しました');
        return back();
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param OfferService $offerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveEditedImage(Request $request, ImageService $imageService, OfferService $offerService)
    {
        $this->validate($request, [
            'image_data' => 'required',
            'origin_image_id' => 'required'
        ]);

        $imageId = $request->input('image_id');
        $originImageId = $request->input('origin_image_id');
        $imageData = $request->input('image_data');
        $imageWidth = $request->input('image_width');
        $imageHeight = $request->input('image_height');
        $advertiser = \Auth::guard('advertiser')->user();
        $user = \Auth::user();
        $response = [];

        try {
            $imagePath = $imageService->checkAndSaveImage($imageData, $imageWidth, $imageHeight, $advertiser->id);

            $offerInfo = $offerService->getOfferBaseInfoByImageId($originImageId, $advertiser->id);

            $newImage = $imageService->createOrUpdate(['id' => $imageId], [], [
                'origin_author_id' => $offerInfo->author_id,
                'image_url' => url($imagePath),
                'offer_id' => $offerInfo->offer_id,
                'advertiser_id' => $advertiser->id,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'user_id' => $user->id
            ]);

            $response = $newImage->toArray();
            $response['post_id'] = $offerInfo->post_id;
        } catch (\Exception $e) {
            \Log::error($e);
            return \Response::json($response, 404);
        }

        return \Response::json($response);
    }
}