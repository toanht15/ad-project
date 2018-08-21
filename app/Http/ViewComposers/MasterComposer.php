<?php

namespace App\Http\ViewComposers;

use App\Models\ApprovedNotification;
use App\Service\ApprovedNotificationService;
use Illuminate\View\View;

class MasterComposer
{

    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        /** @var ApprovedNotificationService $notificationService */
        $notificationService = app(ApprovedNotificationService::class);

        $advertiser = \Auth::guard('advertiser')->user();
        $user = \Auth::user();
        if (!$user || !$advertiser) {
            return;
        }

        $notifications = $notificationService->getNotifications($user->id, $advertiser->id);
        $unreadCount = $notificationService->countUnread($user->id, $advertiser->id);

        $view->with([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}
