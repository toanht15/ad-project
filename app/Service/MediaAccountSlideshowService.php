<?php


namespace App\Service;


use App\Repositories\Eloquent\MediaAccountSlideshowRepository;

class MediaAccountSlideshowService extends BaseService {

    /** @var MediaAccountSlideshowRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(MediaAccountSlideshowRepository::class);
    }

    /**
     * @param $mediaId
     * @return mixed
     */
    public function getMediaSlideshowByMediaId($mediaId)
    {
        return $this->repository->getMediaSlideshowByMediaId($mediaId);
    }
}