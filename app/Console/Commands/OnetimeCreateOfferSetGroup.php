<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\OfferSet;
use App\Models\OfferSetGroup;
use App\Models\Post;
use Illuminate\Console\Command;

class OnetimeCreateOfferSetGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onetimeCreateOfferSetGroup';

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
        //オファーセットグループと言う新しい階段があった
        $offerSets = OfferSet::all();

        foreach ($offerSets as $offerSet) {
            $approvedImageCount = Post::join('offers', 'offers.post_id', '=', 'posts.id')
                ->join('offer_sets', 'offer_sets.id', '=', 'offers.offer_set_id')
                ->distinct('posts.id')
                ->where('offer_sets.id', $offerSet->id)
                ->where('offers.status', Offer::STATUS_APPROVED)
                ->count();

            $offerSetGroup = new OfferSetGroup();
            $offerSetGroup->title = $offerSet->title;
            $offerSetGroup->advertiser_id = $offerSet->advertiser_id;
            $offerSetGroup->approved_image_count = $approvedImageCount;
            $offerSetGroup->offering_image_count = $offerSet->target_count - $approvedImageCount;
            $offerSetGroup->save();

            $offerSet->offer_set_group_id = $offerSetGroup->id;
            $offerSet->save();
        }
    }
}
