<?php


namespace App\Service;


use App\Repositories\Eloquent\TwitterStatJobRepository;
use Hborras\TwitterAdsSDK\TwitterAds\Analytics\Job;

class TwitterStatJobService extends BaseService {

    /** @var TwitterStatJobRepository  */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(TwitterStatJobRepository::class);
    }

    /**
     * @param Job $job
     * @param $mediaAccountId
     * @return mixed
     */
    public function createJobFromResponse(Job $job, $mediaAccountId)
    {
        return $this->repository->create([
            'media_account_id'  => $mediaAccountId,
            'job_id'            => $job->getIdStr(),
            'start_time'        => $job->getStartTime() ? $job->getStartTime()->format('Y-m-d H:i:s') : null,
            'end_time'          => $job->getEndTime() ? $job->getEndTime()->format('Y-m-d H:i:s') : null,
            'segmentation_type' => $job->getSegmentationType() ? $job->getSegmentationType() : '',
            'url'               => $job->getUrl() ? $job->getUrl() : '',
            'entity_ids'        => $job->getEntityIds() ? implode(',',$job->getEntityIds()) : '',
            'placement'         => $job->getPlacement() ? $job->getPlacement() : '',
            'expires_at'        => $job->getExpiresAt() ? (new \DateTime($job->getExpiresAt()))->format('Y-m-d H:i:s') : null,
            'status'            => $job->getStatus(),
            'granularity'       => $job->getGranularity(),
            'entity'            => $job->getEntity(),
            'metric_groups'     => implode(',',$job->getMetricGroups())
        ]);
    }

    /**
     * @param Job $job
     * @return mixed
     */
    public function updateJobFromResponse(Job $job)
    {
        return $this->updateModel([
            'url'           => $job->getUrl() ? $job->getUrl() : '',
            'expires_at'    => (new \DateTime($job->getExpiresAt()))->format('Y-m-d H:i:s'),
            'status'        => $job->getStatus()
        ], $job->getIdStr(), 'job_id');
    }
}