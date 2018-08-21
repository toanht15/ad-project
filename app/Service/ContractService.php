<?php
namespace App\Service;

use App\Repositories\Eloquent\ContractServiceRepository;
use App\Repositories\Eloquent\ContractScheduleRepository;

class ContractService extends BaseService
{
    /** @var ContractServiceRepository */
    protected $repository;
    /** @var ContractScheduleRepository */
    protected $contractScheduleRepository;

    public function __construct()
    {
        $this->repository = app(ContractServiceRepository::class);
        $this->contractScheduleRepository = app(ContractScheduleRepository::class);
    }

    /**
     * @param $data
     * @return bool
     */
    public function createContract($data)
    {
        $vtdrSiteId = $data['service_type'] == \App\Models\ContractService::FOR_OWNED ? $data['owned_id'] : null;
        try {
            \DB::beginTransaction();
            $contractService = $this->repository->create([
                'advertiser_id' => $data['advertiser_id'],
                'service_type' => $data['service_type'],
                'vtdr_site_id' => $vtdrSiteId
            ]);
            $this->contractScheduleRepository->create([
                'contract_service_id' => $contractService->id,
                'start_date' => $data['contract_start_date'],
                'end_date' => $data['contract_end_date']
            ]);
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
            return false;
        }
    }

    /**
     * @param $data
     */
    public function updateAdsContract($data)
    {
        $this->contractScheduleRepository->create([
            'contract_service_id' => $data['contract_service_id'],
            'start_date' => $data['contract_start_date'],
            'end_date' => $data['contract_end_date']
        ]);
    }

    /**
     * @param $advId
     * @return mixed
     */
    public function getContractList($advId)
    {
        return $this->repository->findAllBy('advertiser_id', $advId, ['id']);
    }

    /**
     * @param $contractId
     * @return mixed
     */
    public function getLatestSchedule($contractId)
    {
        return $this->repository->getLatestSchedule($contractId);
    }

    /**
     * @return mixed
     */
    public function getActiveVtdrSites()
    {
        return $this->repository->getActiveVtdrSites();
    }

    /**
     * @param $advId
     * @return bool
     * @throws \App\Exceptions\APIRequestException
     */
    public function syncOwnedContract($advId)
    {
        $contract = $this->repository->queryWhere([
            'advertiser_id' => $advId,
            'service_type' => \App\Models\ContractService::FOR_OWNED,
        ])->first();

        if (!$contract) {
            return false;
        }

        $schedule = $this->contractScheduleRepository->findBy('contract_service_id', $contract->id);
        /** @var PartService $partService */
        $partService = app(PartService::class);
        $site = $partService->findSite($contract->vtdr_site_id);
        if ($this->shouldUpdateContract($schedule, $site)) {
            $schedule->start_date = $site->contract_start_at;
            $schedule->end_date = $site->contract_end_at;
            $schedule->save();

            return true;
        }

        return false;
    }

    /**
     * @param $schedule
     * @param $site
     * @return bool
     */
    private function shouldUpdateContract($schedule, $site)
    {
        $startDate = date('Y-m-d', strtotime($schedule->start_date));
        $endDate = date('Y-m-d', strtotime($schedule->end_date));
        if (($endDate != $site->contract_end_at) || ($startDate != $site->contract_start_at)) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function updateOwnedContract($data)
    {
        if (!$data['vtdr_site_id']) {
            throw new \Exception("Invalid vtdr_site_id");
        }
        /** @var PartService $partService */
        $partService = app(PartService::class, ['site_id' => $data['vtdr_site_id']]);
        $partService->setAdmin(true);
        $response = $partService->updateSiteContract($data['contract_start_date'], $data['contract_end_date']);
        if ($response['result'] == 'ok') {
            $this->contractScheduleRepository->createOrUpdate([
                'contract_service_id' => $data['contract_service_id'],
            ], [], [
                'contract_service_id' => $data['contract_service_id'],
                'start_date' => $data['contract_start_date'],
                'end_date' => $data['contract_end_date']
            ]);
        }
    }
}
