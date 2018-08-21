<?php


namespace App\Service;


use App\Repositories\Eloquent\MediaAdRepository;

class MediaAdService extends BaseService {

    protected $repository;

    public function __construct()
    {
        $this->repository = app(MediaAdRepository::class);
    }
}