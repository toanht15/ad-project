<?php


namespace App\Service;


use App\Repositories\Eloquent\MediaTokenRepository;

class MediaTokenService extends BaseService {

    /** @var MediaTokenRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(MediaTokenRepository::class);
    }
}