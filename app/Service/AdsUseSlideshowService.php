<?php


namespace App\Service;


use App\Repositories\Eloquent\AdsUseSlideshowRepository;

class AdsUseSlideshowService extends BaseService {

    protected $repository;

    public function __construct()
    {
        $this->repository = app(AdsUseSlideshowRepository::class);
    }
}