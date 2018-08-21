<?php

namespace App\Console\Commands;

use App\Service\ImageService;
use App\Service\PostService;
use Classes\Constants;
use Classes\InstagramApiClient;
use App\Models\InstagramAccount;
use App\UGCConfig;
use FacebookAds\Exception\Exception;

class ReloadMediaUrl extends BaseCommand
{
    const MAX_REQUEST_PER_HOUR = 5000;
    /** @var  InstagramApiClient */
    private $instagramClient;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reloadMediaUrl';

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
    public function doCommand()
    {
        $this->instagramClient = new InstagramApiClient();
        $this->instagramClient->setToken(InstagramAccount::find(UGCConfig::get('instagram.crawlAccountId'))->access_token);
        $imageService = new ImageService();
        /** @var PostService $postService */
        $postService = app(PostService::class);
        $crawledImages = $imageService->getCrawledImages();
        $requestCount = 0;
        $startTime = time();
        foreach ($crawledImages as $crawledImage) {
            
            $postData = $this->instagramClient->getMedia($crawledImage->post_id);
            if(!$postData) {
                $this->warn('Post '.$crawledImage->post_id.' Not success:  Data not exist');
                continue;
            }

            $postService->storePostData($postData, null);

            $this->info('Post '.$crawledImage->post_id.' success');


            if ($postData->getType() == Constants::IMG_TYPE_TEXT) {
                $imageService->updateImageUrl($crawledImage->id, $postData->getStandardResImage()->url);
            } else {
                $imageService->updateVideoUrl(
                    $crawledImage->id,
                    $postData->getStandardResImage()->url,
                    $postData->getStandardResVideo()->url);
            }

            $requestCount++;

            if ($requestCount > self::MAX_REQUEST_PER_HOUR) {
                $executedTime = time() - $startTime;
                if($executedTime <= 3600) {
                    sleep(3700 - $executedTime);
                    $startTime = time();
                    $requestCount = 0;
                }
            }
        }
        \Log::info('Completed '.(count($crawledImages)).' images');
    }
}
