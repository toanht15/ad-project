<?php


namespace App\Repositories\Eloquent;


use App\Models\ApprovedNotification;

class ApprovedNotificationRepository extends BaseRepository {

    public function modelClass()
    {
        return ApprovedNotification::class;
    }

    /**
     * @param $userId
     * @param $advertiserId
     * @return mixed
     */
    public function getNotifications($userId, $advertiserId)
    {
        return $this->model->join('offer_set_groups', 'approved_notifications.offer_set_group_id', '=', 'offer_set_groups.id')
            ->where([
                'approved_notifications.user_id' => $userId,
                'approved_notifications.advertiser_id' => $advertiserId
            ])->selectRaw('approved_notifications.*, offer_set_groups.title')
            ->orderBy('approved_notifications.created_at', 'desc')
            ->paginate(10);
    }

    /**
     * @param $userId
     * @param $advertiserId
     * @return mixed
     */
    public function countUnread($userId, $advertiserId)
    {
        return $this->model->where([
            'user_id' => $userId,
            'advertiser_id' => $advertiserId,
            'is_read' => false
        ])->count();
    }
}