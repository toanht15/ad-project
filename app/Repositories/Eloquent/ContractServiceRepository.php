<?php

namespace App\Repositories\Eloquent;

use App\Models\ContractService;

class ContractServiceRepository extends BaseRepository {
    public function modelClass()
    {
        return ContractService::class;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getContractByAdvId($id)
    {
        $select = [
            'contract_services.id as contract_service_id',
            'contract_services.advertiser_id',
            'contract_services.vtdr_site_id',
            'contract_services.service_type',
            'contract_schedules.id as schedule_id',
            'contract_schedules.start_date',
            'contract_schedules.end_date'
        ];
        return $this->model->join('contract_schedules', 'contract_schedules.contract_service_id', '=', 'contract_services.id')
            ->where('contract_services.advertiser_id', '=', $id)
            ->orderBy('contract_services.service_type')
            ->select($select)
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOwnedContractByAdvId($id)
    {
        return $this->model->where([
            'advertiser_id' => $id,
            'service_type' => ContractService::FOR_OWNED
        ])
            ->select('vtdr_site_id')
            ->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getLatestSchedule($id)
    {
        return $this->model->join('contract_schedules', 'contract_schedules.contract_service_id', '=', 'contract_services.id')
            ->where('contract_services.id', '=', $id)
            ->orderBy('contract_schedules.end_date', 'desc')
            ->select('contract_schedules.*', 'contract_services.service_type', 'contract_services.vtdr_site_id')
            ->first();
    }

    /**
     * @param $advId
     * @param $serviceType
     * @return mixed
     */
    public function getContractListByServiceType($advId, $serviceType)
    {
        return $this->model->join('contract_schedules', 'contract_schedules.contract_service_id', '=', 'contract_services.id')
            ->where([
                'contract_services.advertiser_id' => $advId,
                'service_type'         => $serviceType
            ])->get();
    }

    /**
     * @return mixed
     */
    public function getActiveVtdrSites()
    {
        $now = date('Y-m-d');
        return $this->model->join('contract_schedules', 'contract_schedules.contract_service_id', '=', 'contract_services.id')
            ->where([
                ['contract_schedules.start_date', '<=', $now],
                ['contract_schedules.end_date', '>=', $now],
                ['contract_services.service_type', '=' , 2]
            ])
            ->groupBy('contract_services.vtdr_site_id')
            ->get();
    }
}