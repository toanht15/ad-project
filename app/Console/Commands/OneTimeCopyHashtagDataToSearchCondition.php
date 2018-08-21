<?php

namespace App\Console\Commands;

use App\Models\AccountHasHashtag;
use App\Models\Hashtag;
use App\Models\SearchCondition;
use App\Models\SearchHashtag;
use Illuminate\Console\Command;

class OneTimeCopyHashtagDataToSearchCondition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneTimeCopyHashtagDataToSearchCondition';

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
        $accountHasHashtags = AccountHasHashtag::join('hashtags', 'account_has_hashtag.hashtag_id', '=', 'hashtags.id')
        ->orderBy('account_has_hashtag.created_at', 'desc')
        ->selectRaw('account_has_hashtag.*, hashtags.hashtag, hashtags.post_count')
        ->get();

        foreach ($accountHasHashtags as $accountHasHashtag) {
            $searchCondition = new SearchCondition();
            $searchCondition->advertiser_id = $accountHasHashtag->ad_account_id;
            $searchCondition->title = strpos($accountHasHashtag->hashtag, 'act_') !== false ? $accountHasHashtag->hashtag : '#'.$accountHasHashtag->hashtag;
            $searchCondition->created_at = $accountHasHashtag->created_at;
            $searchCondition->post_count = $accountHasHashtag->post_count ? $accountHasHashtag->post_count : 0;
            $searchCondition->save();

            $searchHashtag = new SearchHashtag();
            $searchHashtag->search_condition_id = $searchCondition->id;
            $searchHashtag->hashtag_id = $accountHasHashtag->hashtag_id;
            $searchHashtag->save();
        }
    }
}
