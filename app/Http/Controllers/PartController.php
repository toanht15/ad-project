<?php

namespace App\Http\Controllers;

use App\Exceptions\APIRequestException;
use App\Repositories\Eloquent\PartImageTemporaryRepository;
use App\Service\SearchConditionService;
use Classes\Constants;
use Classes\Parts\Part;
use Illuminate\Http\Request;
use App\Service\PartService;
use Illuminate\Support\Facades\Auth;

class PartController extends Controller
{
    const NORMAL = 1;
    const DEMO = 3;
    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCreate(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'title' => 'required|string|max:30',
            'template' => 'required',
        ]);

        try {
            $site = \Session::get('site');
            $result = $partService->createDefaultPart($request->get('title'), $request->get('template'),
                $site->contract_start_at, '00:00:00', $site->contract_end_at, '00:00:00');
            $partId = $result['data']['part']['id'];

            $partDesign = $partService->getPartDesignSetting($partId);
            $partDesignData = $partService->getPartDesignData($partDesign->data, $partId, 'draft');
            $partService->updatePartDesign($partDesignData, $partId);
        } catch (APIRequestException $e) {

            return response()->json(['errors' => $e->getApiErrors()], 400);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'errors' => [Constants::TOASTR_ERROR => 'UGCセットが作成できませんでした']],
                400
            );
        }

        return response()->json();
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $partId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetPartBasicSetting(Request $request, PartService $partService, $partId)
    {
        try {
            $part = $partService->getPartBasicSetting($partId);

            return response()->json($part->toArray());
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([], 400);
        }
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $partId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetPartDesignSetting(Request $request, PartService $partService, $partId)
    {
        try {
            $part = $partService->getPartDesignSetting($partId);

            return response()->json($part->toArray());
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([], 400);
        }
    }

    /**
     * @param PartService $partService
     * @param SearchConditionService $searchConditionService
     * @return \Illuminate\Contracts\View\View
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function listPage(PartService $partService, SearchConditionService $searchConditionService)
    {
        $parts = $partService->all();
        $advertiser = Auth::guard('advertiser')->user();

        $site = \Session::get('site');
        $searchConditionList = $searchConditionService->getSearchConditionList($advertiser->id);
        $pages = $partService->paginateProducts(1)['products'];
        $parts_json = [];
        foreach ($parts as $part) {
            $parts_json[] = $part->deserialize();
        }

        return \View::make('part.part_list', [
                'parts' => $parts,
                'parts_data' => $parts_json,
                'searchConditionList' => $searchConditionList,
                'pages' => $pages,
                'isAdmin' => Auth::guard('admin')->check(),
                'can_create_part' => ($site->parts_limit > count($parts))]
        );
    }

    /**
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function listPartApi(PartService $partService)
    {
        $parts = $partService->all();
        $parts_json = [];
        foreach ($parts as $part) {
            $parts_json[] = $part->deserialize();
        }

        return response()->json($parts_json);
    }


    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, PartService $partService, $id)
    {
        $this->validate($request, [
            'start_date' => 'date',
            'finish_date' => 'required_with:start_date|date',
        ]);
        /** @var Part $part */
        $termFrom = $request->get('start_date');
        $termTo = $request->get('finish_date');
        $display = $request->get('display');
        $part = $partService->findWithDateRange($id, $termFrom, $termTo, $display);
        return response()->json($part->deserialize());
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param SearchConditionService $searchConditionService
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function partDetail(Request $request, PartService $partService, SearchConditionService $searchConditionService, $id)
    {
        $this->validate($request, [
            'start_date' => 'date',
            'finish_date' => 'required_with:start_date|date'
        ]);

        $advertiser = Auth::guard('advertiser')->user();
        $searchConditionList = $searchConditionService->getSearchConditionList($advertiser->id);
        /** @var Part $part */
        $termFrom = $request->get('start_date');
        $termTo = $request->get('finish_date');
        $part = $partService->findWithDateRange($id, $termFrom, $termTo);

        list($dateStart, $dateStop) = get_request_datetime($request);

        return \View::make('part.part_detail',
            ['part' => $part, 'dateStart' => $dateStart,
                'dateStop' => $dateStop,
                'searchConditionList' => $searchConditionList
            ]);
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function kpi(Request $request, PartService $partService, $id)
    {
        $this->validate($request, [
            'start_date' => 'required|date',
            'finish_date' => 'required|date'
        ]);

        $start_date = $request->get('start_date');
        $end_date = $request->get('finish_date');
        /** @var Part $part */
        $results = $partService->getPartKpi($id, $start_date, $end_date);

        $response = [];
        foreach ($results as $result)
            $response[] = $result->deserialize();

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSortValue(Request $request, PartService $partService, $id)
    {
        $this->validate($request, [
            'values.*' => 'required|integer|max:10000'
        ]);
        $values = $request->get('values');
        $partService->updateSortValue($id, $values);
        return response()->json(['result' => True]);
    }


    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, PartService $partService, $id)
    {
        /** @var Part $part */
        $partService->deleteModel($id);
        return response()->json(['result' => True]);
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBasicSetting(Request $request, PartService $partService, $id)
    {
        $this->validate($request, [
            'title' => 'required|max:30'
        ]);
        $startAt = new \DateTime($request->get('start_at_date'));
        $closeAt = new \DateTime($request->get('close_at_date'));
        $site = \Session::get('site');

        // validate contract period
        if ($startAt->format('Y-m-d') < $site->contract_start_at || $closeAt->format('Y-m-d') > $site->contract_end_at) {
            return response()->json([
                'errors' => [
                    'start_at_date' => '表示期間は契約期間外を設定できません'
                ]
            ], 400);
        }

        $data = $request->all();
        $data['start_at_date'] = $startAt->format('Y-m-d');
        $data['start_at_time'] = $startAt->format('H:i:s');
        $data['close_at_date'] = $closeAt->format('Y-m-d');
        $data['close_at_time'] = $closeAt->format('H:i:s');
        try {
            $partService->updateModel($data, $data['parts_id']);

        } catch (APIRequestException $e) {

            return response()->json(['errors' => $e->getApiErrors()], 404);

        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'errors' => [Constants::TOASTR_ERROR => 'UGCセットが更新できませんでした']],
                400
            );
        }

        return response()->json([]);
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePartDesign(Request $request, PartService $partService, $id)
    {
        $data = $request->all();
        $partStatus = $request->input('part_status');
        $publishType = $partStatus == self::NORMAL ? 'publish' : 'draft';
        $data = $partService->getPartDesignData($data, $id, $publishType);

        try {
            $result = $partService->updatePartDesign($data, $id);
            if (isset($request->all()['height'])) {
                //update height
                $this->updateBasicSetting($request, $partService, $id);
            }

        } catch (APIRequestException $e) {

            return response()->json(['errors' => $e->getApiErrors()], 404);

        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'errors' => [Constants::TOASTR_ERROR => 'UGCセットが更新できませんでした']],
                400
            );
        }

        return response()->json();
    }

    /**
     * @param PartService $partService
     * @param $postId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetPartImageDetail(PartService $partService, $postId)
    {
        try {
            $data = $partService->getPartImageDetail($postId);

            return response()->json($data['data']);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([], 400);
        }
    }

    /**
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAllParts(PartService $partService)
    {
        try {
            $parts = $partService->all();
            $response = [];
            foreach ($parts as $part) {
                $response[] = $part->toArray();
            }
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([], 400);
        }
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerImage(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'part_id' => 'required'
        ]);
        $postId = $request->input('post_id');
        $partId = $request->input('part_id');
        try {
            $partService->registerImage([$partId], [$postId]);

        } catch (APIRequestException $e) {
            $errors = $e->getApiErrors();
            $firstError = array_shift(array_values($errors));
            $request->session()->flash(Constants::ERROR_MESSAGE, $firstError);

            return back();
        } catch (\Exception $e) {
            $request->session()->flash(Constants::ERROR_MESSAGE, "UGCの追加登録を失敗しました");
            return back();
        }
        $request->session()->flash(Constants::INFO_MESSAGE, "UGCの追加登録しました");
        return back();
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiRegisterImages(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'post_ids' => 'required|array',
            'part_ids' => 'required|array'
        ]);
        $postIds = $request->get('post_ids');
        $partIds = $request->get('part_ids');
        try {
            $partService->registerImage($partIds, $postIds);
        } catch (APIRequestException $e) {
            $errors = $e->getApiErrors();
            if (count($errors) === 0) {
                $firstError = '登録できませんでした';
            } else {
                $firstError = array_values($errors)[0];
            }

            $data = [
                'errors' => [
                    Constants::TOASTR_ERROR => $firstError
                ]
            ];
            return response()->json($data, 400);
        }

        return response()->json([]);
    }

    /**
     * @param $postId
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     * @throws APIRequestException
     */
    public function apiGetImageDetail($postId, PartService $partService)
    {
        $imageDetail = $partService->getImageDetailByPostId($postId);

        return response()->json($imageDetail->toArray());
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePageImage(Request $request, PartService $partService)
    {
        try {
            $partService->deletePageImageByPostId($request->input('postId'), $request->input('url'));

            return response()->json([], 200);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([], 400);
        }
    }

    /**
     * @param Request $request
     * @param PartService $partService
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePartImage(Request $request, PartService $partService)
    {
        $this->validate($request, [
            'postId' => 'required',
            'partId' => 'required'
        ]);
        try {
            $partService->deletePartImage($request->input('postId'), $request->input('partId'));

            return response()->json([], 200);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([], 400);
        }
    }

    /**
     * @param PartService $partService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(PartService $partService, $id)
    {
        try {
            $partService->publish($id);
            $partDesign = $partService->getPartDesignSetting($id);
            $partDesignData = $partService->getPartDesignData($partDesign->data, $id, 'publish');
            $partService->updatePartDesign($partDesignData, $id);
        } catch (APIRequestException $e) {
            return response()->json(['errors' => $e->getApiErrors()], 404);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'errors' => [Constants::TOASTR_ERROR => '公開できませんでした']],
                400
            );
        }

        return response()->json([], 200);
    }
}
