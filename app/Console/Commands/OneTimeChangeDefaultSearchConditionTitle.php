<?php

namespace App\Console\Commands;

use App\Service\SearchConditionService;
use Illuminate\Console\Command;

class OneTimeChangeDefaultSearchConditionTitle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneTimeChangeDefaultSearchConditionTitle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var SearchConditionService $searchConditionService */
        $searchConditionService = app(SearchConditionService::class);
        $searchConditions = $searchConditionService->getWhere([
            'title' => [
                'like',
                'act\_%'
            ]
        ]);

        foreach ($searchConditions as $searchCondition) {
            $searchConditionService->updateModel([
                'title' => SearchConditionService::getDefaultSearchConditionTitle($searchCondition->advertiser_id)
            ], $searchCondition->id);
        }
    }
}
