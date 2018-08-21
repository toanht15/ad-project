<?php


namespace App\Service;


use App\Repositories\Eloquent\MediaImageEntryRepository;

class MediaImageEntryService extends BaseService {

    /** @var MediaImageEntryRepository  */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(MediaImageEntryRepository::class);
    }

    public function getFBImageEntryWithImageInfo($mediaAccountId)
    {
        return $this->repository->getFBImageEntryWithImageInfo($mediaAccountId);
    }
}