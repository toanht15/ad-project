<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Eloquent\ContractServiceRepository;
use App\Service\AdvertiserService;
use App\Service\ContractService;
use Classes\Constants;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    /**
     * @param $advId
     * @return \Illuminate\Contracts\View\View
     */
    public function detail($advId, AdvertiserService $advertiserService)
    {
        $advertiser = $advertiserService->getAdvertiserWithTenant($advId);

        return view()->make('admin.contract_detail', [
            'advertiser' => $advertiser,
        ]);
    }

    /**
     * @param Request $request
     * @param ContractService $contractService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, ContractService $contractService)
    {
        $this->validate($request, [
            'advertiser_id' => 'required',
            'service_type' => 'required',
            'contract_start_date' => 'required',
            'contract_end_date' => 'required',
        ]);
        if (!$contractService->createContract($request->input())) {
            $request->session()->flash(Constants::ERROR_MESSAGE, "作成に失敗しました。");
            return back();
        }

        $request->session()->flash(Constants::INFO_MESSAGE, "作成しました。");

        return back();
    }

    /**
     * @param Request $request
     * @param ContractService $contractService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ContractService $contractService)
    {
        $this->validate($request, [
            'contract_service_id' => 'required',
            'contract_start_date' => 'required',
            'contract_end_date' => 'required',
        ]);
        try {
            if ($request->input('service_type') == \App\Models\ContractService::FOR_AD) {
                $contractService->updateAdsContract($request->input());
                $request->session()->flash(Constants::INFO_MESSAGE, "更新しました。");

                return back();
            }

            if ($request->input('service_type') == \App\Models\ContractService::FOR_OWNED) {
                $contractService->updateOwnedContract($request->input());
                $request->session()->flash(Constants::INFO_MESSAGE, "更新しました。");

                return back();
            }
        } catch (\Exception $exception) {
            \Log::error($exception);
            $request->session()->flash(Constants::ERROR_MESSAGE, "更新に失敗しました");
        }

        return back();
    }

    /**
     * @param $contractId
     * @param ContractService $contractService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetLatestSchedule($contractId, ContractService $contractService)
    {
        $schedule = $contractService->getLatestSchedule($contractId);

        return response()->json($schedule);
    }

    /**
     * @param $advId
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAllContracts($advId)
    {
        /** @var ContractServiceRepository $contractServiceRepository */
        $contractServiceRepository = app(ContractServiceRepository::class);
        $contracts = $contractServiceRepository->findAllBy('advertiser_id', $advId, ['id']);

        return response()->json($contracts);
    }

    public function apiSyncOwnedContract(ContractService $contractService, $advId)
    {
        if ($contractService->syncOwnedContract($advId)) {
            return response()->json("synced");
        };

        return response()->json();
    }

    /**
     * @param $advId
     * @param AdvertiserService $advertiserService
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetContractSchedule($advId, AdvertiserService $advertiserService)
    {
        $schedules = $advertiserService->getContractSchedules($advId);

        return response()->json($schedules, 200);
    }
}
