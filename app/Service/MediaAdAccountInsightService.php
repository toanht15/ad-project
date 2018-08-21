<?php


namespace App\Service;


use App\Repositories\Eloquent\MediaAdAccountInsightRepository;

class MediaAdAccountInsightService extends BaseService {

    /** @var MediaAdAccountInsightRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(MediaAdAccountInsightRepository::class);
    }
}