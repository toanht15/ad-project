<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CrawlAccountPostsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $accountId;
    protected $limit;

    /**
     * Create a new job instance.
     *
     * @param $accountId
     * @param $limit
     */
    public function __construct($accountId, $limit)
    {
        $this->accountId = $accountId;
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Artisan::call('instagramAccountCrawler', [
            'accountId' => $this->accountId,
            'limit' => $this->limit
        ]);
    }
}
