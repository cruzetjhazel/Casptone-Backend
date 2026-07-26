<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $notifications = NotificationResource::collection(
            $request->user()->notifications()->latest()->paginate(20)
        );

        return $this->success($notifications->response()->getData(true));
    }

    public function unreadCount(Request $request)
    {
        return $this->success(['unread_count' => $request->user()->unreadNotifications()->count()]);
    }

    public function markAsRead(Request $request, string $notification)
    {
        $record = $request->user()->notifications()->findOrFail($notification);
        $record->markAsRead();

        return $this->success(new NotificationResource($record->fresh()), 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success(null, 'All notifications marked as read.');
    }
}