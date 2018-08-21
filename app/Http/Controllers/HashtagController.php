<?php
namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\SearchCondition;
use App\Service\SearchConditionService;
use \Auth;
use Classes\Constants;
use Illuminate\Http\Request;

/**
 * クローラー用の検索ハッシュタグ管理
 * Class HashtagController
 * @package App\Http\Controllers
 */
class HashtagController extends Controller
{
    /**
     * 一覧取得
     * @param SearchConditionService $searchConditionService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listPage(SearchConditionService $searchConditionService)
    {
        $advertiser = Auth::guard('advertiser')->user();
        $searchConditionList = $searchConditionService->getSearchConditionList($advertiser->id);
        $defaultSearchCondition = $searchConditionService->getDefaultSearchCondition($advertiser->id);

        return view()->make('hashtag.hashtag_list',
            [
                'searchConditionList' => $searchConditionList,
                'defaultSearchCondition' => $defaultSearchCondition,
                'maxCount' => $advertiser->max_search_condition,
                'advertiser' => $advertiser,
                'isAdmin' => Auth::guard('admin')->check()
            ]
        );
    }


    /**
     * @param SearchConditionService $searchConditionService
     * @param $searchConditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiStatistic(SearchConditionService $searchConditionService, $searchConditionId)
    {
        list($allCount , $offeringCount, $approvedCount, $livingCount, $failedCount, $archivedCount) = $searchConditionService->statisticUGC($searchConditionId);
        $unOffer = $allCount - ($offeringCount + $approvedCount + $livingCount + $failedCount + $archivedCount);

        return response()->json([
            'allCount' => $allCount,
            'offeredCount' => $offeringCount + $approvedCount + $livingCount,
            'approvedCount' => $approvedCount + $livingCount,
            'livingCount' => $livingCount,
            'unOfferCount' => $unOffer
        ]);
    }

    /**
     * @param $conditionId
     * @param Request $request
     * @param SearchConditionService $searchService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPreview($conditionId, Request $request, SearchConditionService $searchService)
    {
        $postList = $searchService->search($conditionId, [], $request->input('limit'));

        return \Response::json($postList->toArray());
    }

    /**
     * @param Request $request
     * @param SearchConditionService $searchService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createSearchCondition(Request $request, SearchConditionService $searchService)
    {
        $this->validate($request, [
            'hashtags' => 'required|array'
        ]);

        $hashtags = $request->input('hashtags');
        $nextUrl = $request->input('next_url');
        foreach ($hashtags as $hashtag) {
            if (has_special_character($hashtag)) {
                $request->session()->flash(Constants::ERROR_MESSAGE, 'ハッシュタグは正しくありません');
                return back();
            }
        }

        try {
            $advertiser = Auth::guard('advertiser')->user();
            $newCondition = $searchService->createSearchCondition($advertiser->id, $hashtags);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());

            return back();
        }

        $request->session()->flash('load_async', true);
        $request->session()->flash('new_condition_id', $newCondition->id);

        if ($nextUrl) {
            return redirect()->to($nextUrl);
        }

        return redirect()->route('image_list', ['hashtagId' => $newCondition->id]);
    }

    /**
     * @param Request $request
     * @param $id
     * @param SearchConditionService $searchService
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id, SearchConditionService $searchService)
    {
        $advertiser = Auth::guard('advertiser')->user();
        $searchService->deleteSearchCondition($id, $advertiser->id);

        $request->session()->flash(Constants::INFO_MESSAGE, '削除しました');
        return back();
    }
}
