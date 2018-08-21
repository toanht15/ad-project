<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CrawlHashtagPostsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $hashtagId;
    protected $limit;

    /**
     * Create a new job instance.
     *
     * @param $hashtagId
     * @param $limit
     */
    public function __construct($hashtagId, $limit)
    {
        $this->hashtagId = $hashtagId;
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Artisan::call('instagramHashtagCrawler', [
            'hashtagId' => $this->hashtagId,
            'limit' => $this->limit
        ]);
    }
}
