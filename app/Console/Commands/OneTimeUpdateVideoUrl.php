<?php

namespace App\Console\Commands;

use App\Repositories\Eloquent\ImageRepository;

class OneTimeUpdateVideoUrl extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneTimeUpdateVideoUrl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update video url in metarials table';

    public function doCommand()
    {
        /** @var ImageRepository $imageRepository */
        $imageRepository = app(ImageRepository::class);
        $videos = $imageRepository->getAllVideoWithPost();
        foreach ($videos as $video) {
            if ($video->image_video_url != $video->post_video_url) {
                $imageRepository->update(['video_url' => $video->post_video_url], $video->video_id);
            }
        }
    }
}
