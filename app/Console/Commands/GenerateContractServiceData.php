<?php

namespace App\Console\Commands;

use App\Models\ContractService;
use App\Repositories\Eloquent\AdvertiserRepository;
use App\Repositories\Eloquent\ContractScheduleRepository;
use App\Repositories\Eloquent\ContractServiceRepository;

class GenerateContractServiceData extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateContractServiceData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate contract service default data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function doCommand()
    {
        $advertiserRepository       = app(AdvertiserRepository::class);
        $contractServiceRepository  = app(ContractServiceRepository::class);
        $contractScheduleRepository = app(ContractScheduleRepository::class);
        $advertisers                = $advertiserRepository->all();

        foreach ($advertisers as $advertiser) {
            try {
                \DB::beginTransaction();

                $contractService = $contractServiceRepository->create([
                    'advertiser_id' => $advertiser->id,
                    'service_type'  => ContractService::FOR_AD
                ]);

                $contractScheduleRepository->create([
                    'contract_service_id' => $contractService->id,
                    'start_date'          => '2015-01-01',
                    'end_date'            => '2020-12-31'
                ]);

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error($e);
            }
        }
    }
}
