<?php


namespace App\Console\Commands;


use App\Service\MediaAccountSlideshowService;
use App\Service\SlideshowService;

class OneTimeCopySlideshowToMediaSlideshow extends BaseCommand {

    protected $signature = 'oneTimeCopySlideshowToMediaSlideshow';

    public function doCommand()
    {
        /** @var SlideshowService $slideshowService */
        $slideshowService = app(SlideshowService::class);
        /** @var MediaAccountSlideshowService $mediaSlideshowService */
        $mediaSlideshowService = app(MediaAccountSlideshowService::class);

        $uploadedSlideshows = $slideshowService->getWhere([
            'fb_video_id' => [
                '!=',
                ''
            ]
        ]);

        foreach($uploadedSlideshows as $slideshow) {
            $mediaSlideshowService->createOrUpdate([
                'id' => $slideshow->id
            ],[
                'id' => $slideshow->id,
                'media_account_id' => $slideshow->advertiser_id,
                'slideshow_id' => $slideshow->id,
                'media_object_id' => $slideshow->fb_video_id,
            ]);
        }
    }
}