<?php


namespace App\Service;


use App\Repositories\Eloquent\ApprovedNotificationRepository;

class ApprovedNotificationService extends BaseService {

    protected $repository;

    public function __construct(ApprovedNotificationRepository $approvedNotificationRepository)
    {
        $this->repository = $approvedNotificationRepository;
    }

    /**
     * @param $userId
     * @param $advertiserId
     * @return mixed
     */
    public function getNotifications($userId, $advertiserId)
    {
        return $this->repository->getNotifications($userId, $advertiserId);
    }

    /**
     * @param $userId
     * @param $advertiserId
     * @return mixed
     */
    public function countUnread($userId, $advertiserId)
    {
        return $this->repository->getNotifications($userId, $advertiserId)->count();
    }
}