<?php

namespace App\Http\Controllers\Admin;

use App\Service\AdvertiserService;
use App\Service\ContractService;
use App\Service\SearchConditionService;
use App\Service\TenantService;
use App\Service\UgcKpiService;
use App\Service\PartService;
use App\Repositories\Eloquent\ContractServiceRepository;
use Classes\Constants;
use Classes\ErrorMessage;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class AdAccountController extends Controller
{
    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @param UgcKpiService $ugcService
     * @param TenantService $tenantService
     * @return \Illuminate\Contracts\View\View
     */
    public function listPage(Request $request, AdvertiserService $advertiserService, UgcKpiService $ugcService, TenantService $tenantService)
    {
        list($dateStart, $dateStop) = get_request_datetime($request);
        $yesterday = (new \DateTime())->modify('-1 day')->format('Y-m-d');

        $adAccounts = $advertiserService->getAdAccountInfo($dateStart, $dateStop);
        $ugcSpend = $ugcService->getUgcSpendGroupByAdAccount($dateStart, $dateStop);
        $yesterdayUgcSpend = $ugcService->getUgcSpendGroupByAdAccount($yesterday, $yesterday);

        $tenants = $tenantService->all();

        return view()->make('admin.ad_account_list', [
            'adAccounts' => $adAccounts,
            'dateStart' => $dateStart,
            'dateStop' => $dateStop,
            'ugcSpend' => $ugcSpend,
            'yesterdayUgcSpend' => $yesterdayUgcSpend,
            'tenantList' => $tenants
        ]);
    }

    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, AdvertiserService $advertiserService)
    {
        $this->validate($request, [
            'name' => 'required',
            'tenant_id' => 'required'
        ]);

        try {
            $advertiserService->createNewAdvertiser($request->get('name'), $request->get('tenant_id'));
            $request->session()->flash(Constants::INFO_MESSAGE, '新しいアドバタイザーが追加されました。');
        } catch (\Exception $e) {
            $request->session()->flash(Constants::ERROR_MESSAGE, '失敗しました。');
        }

        return back();
    }

    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateInfo(Request $request, AdvertiserService $advertiserService)
    {
        $this->validate($request, [
            'id' => 'required',
            'max_search_condition' => 'required',
            'max_media_account' => 'required'
        ]);
        $advertiser = $advertiserService->findModel($request->input('id'));
        if (!$advertiser) {
            $request->session()->flash(Constants::ERROR_MESSAGE, ErrorMessage::INVALID_REQUEST);
        } else {
            $advertiserService->updateModel([
                'max_search_condition' => $request->input('max_search_condition'),
                'completed_tutorial_flg' => $request->input('completed_tutorial_flg'),
                'max_media_account' => $request->input('max_media_account')
            ], $advertiser->id);
            $request->session()->flash(Constants::INFO_MESSAGE, '保存しました');
        }

        return back();
    }

    /**
     * @param AdvertiserService $advertiserService
     * @param ContractService $contractService
     * @param $advertiserId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAsAdvertiser(AdvertiserService $advertiserService, $advertiserId)
    {
        if (!\Auth::check()) {
            \Auth::login(\Auth::guard('admin')->user());
        }
        $advertiser = $advertiserService->findModel($advertiserId);

        try {
            if (\Session::has('site')) {
                \Session::forget('site');
            }
            if (\Session::has('canUseAds')) {
                \Session::forget('canUseAds');
            }

            try {
                // check contract info
                $info = $advertiserService->getContractInfo($advertiserId);
                $canUseAds = $info['adsContract'] ? true : false;
                $canUsePost = $info['post'] ? true : false;

                \Session::put('post', $canUsePost);
                \Session::put('canUseAds', $canUseAds);
                if (isset($info['site']) && isset($info['isOwnedFirst'])) {
                    \Session::put('site', $info['site']);
                    \Session::put('isOwnedFirst', $info['isOwnedFirst']);
                }
            } catch (\Exception $e) {
                \Log::error($e);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

        \Auth::guard('advertiser')->login($advertiser);

        if ($canUsePost && !($canUseAds || isset($info['site']))) {
            return Redirect::to(env('POST_DOMAIN'));
        }

        return redirect()->route('dashboard');
    }

    /**
     * @param Request $request
     * @param AdvertiserService $advertiserService
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request, AdvertiserService $advertiserService, $id)
    {
        $this->validate($request, [
            'adaccount_name' => 'required'
        ]);

        $accountName = $request->input('adaccount_name');
        try {
            $advertiserService->removeAdAccount($id, $accountName);
            $request->session()->flash(Constants::INFO_MESSAGE, '【' . $accountName . '】を削除しました。');

        } catch (\Exception $e) {
            $request->session()->flash(Constants::ERROR_MESSAGE, $e->getMessage());
        }

        return back();
    }
}
