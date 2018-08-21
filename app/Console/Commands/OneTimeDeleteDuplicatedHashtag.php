<?php

namespace App\Console\Commands;

use App\Models\Hashtag;
use App\Models\SearchCondition;
use Illuminate\Console\Command;

class OneTimeDeleteDuplicatedHashtag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneTimeDeleteDuplicatedHashtag';

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
        $duplicatedHashtag = Hashtag::where('active_flg', 0)->groupBy('hashtag')->havingRaw('count(id)>1')->pluck('hashtag');
        $hashtags = Hashtag::whereIn('hashtag', $duplicatedHashtag)
        ->where([
            'active_flg'    => 0
        ])->limit(5000)->get();

        foreach ($hashtags as $hashtag) {
            try {
                \DB::beginTransaction();
                $hashtag->fDelete();
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Could not delete hashtag '.$hashtag->id);
            }
        }
    }
}
