<?php

namespace App\Http\Controllers;

use App\Models\ArchivedPost;
use App\Models\Author;
use App\Models\Hashtag;
use App\Models\InstagramAccount;
use App\Models\OfferSetGroup;
use App\Models\SearchCondition;
use App\Models\SearchHashtag;
use App\Models\Slideshow;
use App\Service\ArchivedPostService;
use App\Service\MediaImageEntryService;
use App\Service\ImageService;
use App\Service\InstagramAccountService;
use App\Service\MediaAccountService;
use App\Service\OfferService;
use App\Service\OfferSetGroupService;
use App\Service\PostService;
use App\Service\SearchConditionService;
use App\Service\SlideshowService;
use Classes\Constants;
use Classes\InstagramApiClient;
use Illuminate\Http\Request;
use App\Models\MediaImageEntry;
use App\Models\HashtagHasPost;
use App\Models\Image;
use App\Models\Offer;
use App\Models\Post;
use \Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;

class ImageController extends Controller
{
    const UGC_PER_PAGE = 24;

    /**
     * @param Request $request
     * @param SlideshowService $slideshowService
     * @param MediaAccountService $mediaAccountService
     * @param SearchConditionService $searchConditionService
     * @param InstagramAccountService $instagramAccountService
     * @param OfferSetGroupService $offerSetGroupService
     * @param null $searchConditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function listPage(Request $request, SlideshowService $slideshowService, MediaAccountService $mediaAccountService,
                             SearchConditionService $searchConditionService, InstagramAccountService $instagramAccountService,
                             OfferSetGroupService $offerSetGroupService, $searchConditionId = null)
    {
        $status = $request->get('status');
        $advertiser = Auth::guard('advertiser')->user();
        $isAdmin = Auth::guard('admin')->check();

        $searchConditionList = $searchConditionService->getWhere([
            'advertiser_id' => $advertiser->id
        ], false, ['created_at', 'desc']);

        if (!$isAdmin && count($searchConditionList) <= 1) {
            //craw用タグなし
            return redirect()->route('dashboard');
        }

        $mediaAccountList = [];
        if (!$isAdmin) {
            //デフォルトのタグを外す
            $defaultSearchCondition = $searchConditionList->pop();
        } else {
            $defaultSearchCondition = $searchConditionList->last();
            $mediaAccountList = $mediaAccountService->getMediaAccountsWithToken($advertiser->id);
        }

        if (!$searchConditionId) {
            $searchConditionId = $request->session()->get('new_condition_id');
        }
        if ($searchConditionId) {
            $searchCondition = $searchConditionService->getWhere([
                'id' => $searchConditionId,
                'advertiser_id' => $advertiser->id
            ])->first();
        } else {
            $searchCondition = null;
        }

        $allUgcCount = $searchConditionService->countAllUgcByAdAccount($advertiser->id);
        if (!$advertiser->completed_tutorial_flg) {
            $shouldShowTutorial = $allUgcCount;
        } else {
            $shouldShowTutorial = false;
        }
        $offerSetGroups = $offerSetGroupService->getWhere(['advertiser_id' => $advertiser->id]);

        $instagramAccounts = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id);

        return \View::make('image.images', [
            'advertiser' => $advertiser,
            'searchConditionList' => $searchConditionList->toArray(),
            'status' => $status,
            'offerSetGroups' => $offerSetGroups,
            'shouldShowTutorial' => $shouldShowTutorial,
            'currentSearchCondition' => $searchCondition,
            'loadAsync' => $request->session()->get('load_async'),
            'instagramAccount' => $instagramAccounts->first(),
            'isAdmin' => $isAdmin,
            'allUgcCount' => $allUgcCount,
            'mediaAccountList' => $mediaAccountList,
            'slideshowId' => $request->get('slideshow_id'),
            'defaultSearchCondition' => $defaultSearchCondition
        ]);
    }

    /**
     * @param Request $request
     * @param SearchConditionService $searchConditionService
     * @param ArchivedPostService $archivedPostService
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function apiGetImageList(Request $request, SearchConditionService $searchConditionService, ArchivedPostService $archivedPostService)
    {
        $searchConditionId = $request->input('search_condition_id');
        $status = $request->input('status');
        $partId = $request->input('part_id');
        $order = $request->input('order');
        $orderArr = ['like', 'pub_date', 'approved_at'];
        if (!$order || !in_array($order, $orderArr)) {
            if ($status == Offer::STATUS_ARCHIVE) {
                $order = 'archived_posts.created_at';
            } else {
                $order = 'pub_date';
            }
        }
        $imagePerPage = $request->get('limit') ? $request->get('limit') : static::UGC_PER_PAGE;

        $conditions = [];

        if (can_use_ugc_set()) {
            $conditions['siteId'] = \Session::get('site')->id;
            if ($partId) {
                $conditions['partIds'] = [$partId];
            }
        }

        $advertiser = Auth::guard('advertiser')->user();
        if (in_array($status, [Offer::STATUS_APPROVED, Offer::STATUS_LIVING, Offer::STATUS_COMMENT_FALSE])) {
            $conditions['status'] = [$status];
            $imageList = $searchConditionService->search($searchConditionId, $conditions, $imagePerPage, false, $order);

        } elseif ($status == Offer::STATUS_COMMENTED) {
            $conditions['status'] = [Offer::STATUS_COMMENTED, Offer::STATUS_OFFERED];
            $imageList = $searchConditionService->search($searchConditionId, $conditions, $imagePerPage, false, $order);

        } elseif ($status == Offer::STATUS_ARCHIVE) {
            $imageList = $archivedPostService->getArchivedPost($advertiser->id, false, $imagePerPage, $order);

        } else {
            $imageList = $searchConditionService->search($searchConditionId, $conditions, $imagePerPage, false, $order);
        }

        if ($searchConditionId || $status == Offer::STATUS_ARCHIVE) {
            $response = $imageList->toArray();
        } else {
            // paginate after use union
            $imageList = $imageList->toArray();
            $page = Input::get('page', 1);

            $offSet = ($page * $imagePerPage) - $imagePerPage;
            $itemsForCurrentPage = array_slice($imageList, $offSet, $imagePerPage, false);
            $response = new LengthAwarePaginator($itemsForCurrentPage, count($imageList), $imagePerPage, $page);
            $response = $response->toArray();
        }

        if ($request->input('get_crawling_flg')) {
            $crawlingCount = $searchConditionService->countCrawlingHashtag($searchConditionId);
            $response['crawlingFlg'] = $crawlingCount ? true : false;
        }

        if ($request->wantsJson()) {
            return response()->json($response);
        } else {
            return view()->make('templates.image_list', $response);
        }
    }

    /**
     * @param Request $request
     * @param SearchConditionService $searchConditionService
     * @param ArchivedPostService $archivedPostService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetStatistic(Request $request, SearchConditionService $searchConditionService, ArchivedPostService $archivedPostService)
    {
        $searchConditionId = $request->input('search_condition_id');
        $advertiser = Auth::guard('advertiser')->user();

        list($allCount, $offeringCount, $approvedCount, $livingCount, $failedCount) = $searchConditionService->statisticUGC($searchConditionId);

        $isCrawling = $searchConditionService->countCrawlingHashtag($searchConditionId);

        $archivedCount = $archivedPostService->getArchivedPost($advertiser->id, true);

        $response['allCount'] = $allCount;
        $response['approvedCount'] = $approvedCount;
        $response['offeringCount'] = $offeringCount;
        $response['livingCount'] = $livingCount;
        $response['failedCount'] = $failedCount;
        $response['archivedCount'] = $archivedCount;
        $response['isCrawling'] = $isCrawling;

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param $imageId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteImage(Request $request, ImageService $imageService, $imageId)
    {
        $image = $imageService->findModel($imageId);

        if ($imageService->isUsedForSlideshow($image)) {
            $request->session()->flash(Constants::ERROR_MESSAGE, 'この画像はスライドショーで使用しているため削除できません');
            return back();
        }

        $imageService->deleteModel($image->id);

        $request->session()->flash(Constants::INFO_MESSAGE, '削除しました');

        return back();
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param SearchConditionService $searchService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request, ImageService $imageService, SearchConditionService $searchService)
    {
        $images = $request->file('images');

        if (!$images) {
            return redirect()->route('image_list');
        }

        $rule = ['images' => 'required|array'];
        foreach ($images as $key => $image) {
            $rule['images.' . $key] = 'required|image|max:4000';
        }
        $this->validate($request, $rule);

        $advertiser = Auth::guard('advertiser')->user();

        $defaultHashtag = $imageService->uploadImageToDefaultHashtag($images, $advertiser);

        $searchService->updateSearchConditionResultCount($defaultHashtag);

        return redirect()->route('image_list');
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param MediaAccountService $mediaAccountService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getImageFromFacebookLibrary(Request $request, ImageService $imageService, MediaAccountService $mediaAccountService)
    {
        $this->validate($request, [
            'hashcode_list' => 'required',
            'media_account_id' => 'required'
        ]);

        $user = Auth::user();

        $hashcodeList = str_replace("\r\n", "\n", $request->input('hashcode_list'));
        $hashcodeList = explode("\n", $hashcodeList);

        try {
            $mediaAccount = $mediaAccountService->findWithToken('id', $request->get('media_account_id'))->first();

            $imageService->getFacebookImage($user, $mediaAccount, $hashcodeList);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());
        }

        return back();
    }

    /**
     * @param Request $request
     * @param ImageService $imageService
     * @param SearchConditionService $searchConditionService
     * @param MediaImageEntryService $mediaImageEntryService
     * @param PostService $postService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteFacebookImages(Request $request, ImageService $imageService, SearchConditionService $searchConditionService,
                                         MediaImageEntryService $mediaImageEntryService, PostService $postService)
    {
        $this->validate($request, [
            'hashcode_list' => 'required'
        ]);

        $advertiser = Auth::guard('advertiser')->user();

        $hashcodeList = str_replace("\r\n", "\n", $request->input('hashcode_list'));
        $hashcodeList = explode("\n", $hashcodeList);

        try {
            $noOfferImages = $imageService->getWhere(['offer_id' => null])->pluck('id')->all();
            // delete fb image
            $mediaImageEntryService->deleteWhere([
                'image_id' => [
                    'in',
                    $noOfferImages
                ]
            ]);
            $imageService->deleteWhere([
                'id' => [
                    'in',
                    $noOfferImages
                ]
            ]);
            $posts = $postService->getBannerPostIds($advertiser->id, $hashcodeList);

            $imageService->deletePost($posts);

            $defaultSearchCondition = $searchConditionService->getDefaultSearchCondition($advertiser->id);
            $defaultHashtag = $searchConditionService->getHashtagBySearchConditionId($defaultSearchCondition->id)->first();
            $searchConditionService->updateSearchConditionResultCount($defaultHashtag);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());
        }

        return back();
    }

    /**
     * @param Request $request
     * @param ArchivedPostService $archivedPostService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function archive(Request $request, ArchivedPostService $archivedPostService)
    {
        $this->validate($request, [
            'archive_ids' => 'required'
        ]);

        $postIds = explode(',', $request->get('archive_ids'));
        $advertiser = Auth::guard('advertiser')->user();
        $user = Auth::user();

        foreach ($postIds as $postId) {
            $archivedPostService->createOrUpdate([
                'advertiser_id' => $advertiser->id,
                'post_id' => $postId
            ], [
                'advertiser_id' => $advertiser->id,
                'post_id' => $postId,
                'created_account_id' => $user->id
            ]);
        }

        $request->session()->flash(Constants::INFO_MESSAGE, '「非表示」に移動しました');

        return redirect()->route('image_list', ['hashtagId' => '', 'status' => Offer::STATUS_ARCHIVE]);
    }

    /**
     * @param Request $request
     * @param ArchivedPostService $archivedPostService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unArchive(Request $request, ArchivedPostService $archivedPostService)
    {
        $this->validate($request, [
            'archive_ids' => 'required'
        ]);

        $postIds = explode(',', $request->get('archive_ids'));
        $archivedPostService->unArchivedPosts(\Auth::guard('advertiser')->user()->id, $postIds);

        $request->session()->flash(Constants::INFO_MESSAGE, '元に戻りました');

        return redirect()->route('image_list', ['hashtagId' => '', 'status' => Offer::STATUS_ARCHIVE]);
    }

    /**
     * @param Request $request
     * @param SearchConditionService $searchService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCountCrawlingHashtag(Request $request, SearchConditionService $searchService)
    {
        $searchConditionId = $request->input('search_condition_id');
        $isCrawling = $searchService->countCrawlingHashtag($searchConditionId);
        $response['isCrawling'] = $isCrawling;

        return response()->json($response);
    }
}
