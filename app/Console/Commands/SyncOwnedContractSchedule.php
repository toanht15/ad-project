<?php

namespace App\Console\Commands;

use App\Service\AdvertiserService;
use App\Service\ContractService;

class SyncOwnedContractSchedule extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncOwnedContractSchedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function doCommand()
    {
        /** @var AdvertiserService $advertiserService */
        $advertiserService = app(AdvertiserService::class);
        $advertisers = $advertiserService->all();
        /** @var ContractService $contractService */
        $contractService = app(ContractService::class);
        foreach ($advertisers as $advertiser) {
            try {
                $contractService->syncOwnedContract($advertiser->id);
            } catch (\Exception $e) {
                \Log::error('AdvertiserId ' . $advertiser->id . ' sync failed');
                \Log::error($e);
            }
        }
    }
}
