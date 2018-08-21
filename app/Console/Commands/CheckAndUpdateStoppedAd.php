<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAndUpdateStoppedAd extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkAndUpdateStoppedAd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function doCommand()
    {
        try{
            $getCampaignKpiCmd = 'php ' . base_path() . '/artisan ' . 'getDailyFbAdsInsight --stoppedAd';
            shell_exec($getCampaignKpiCmd . " > /dev/null");
        } catch (\Exception $e) {
            \Log::error($e);
        }

        try{
            $stopCampaignCmd = 'php ' . base_path() . '/artisan ' . 'updateAdsStatus --stoppedAd';
            shell_exec($stopCampaignCmd . " > /dev/null");
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}
