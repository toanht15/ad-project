<?php

namespace App\Console\Commands;

use App\Models\SearchCondition;
use Illuminate\Console\Command;

class OneTimeUpdateScoreStatus extends BaseCommand
{
    protected $signature = 'oneTimeUpdateScoreStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'description';

    public function doCommand()
    {
        SearchCondition::where('score_status', '=', 3)->update(['score_status' => SearchCondition::STATUS_SCORED]);
    }
}
