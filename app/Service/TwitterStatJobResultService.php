<?php


namespace App\Service;


use App\Repositories\Eloquent\TwitterStatJobResultRepository;

class TwitterStatJobResultService extends BaseService {

    /** @var TwitterStatJobResultRepository  */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(TwitterStatJobResultRepository::class);
    }
}