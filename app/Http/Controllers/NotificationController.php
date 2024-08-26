<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        // Validate the request
        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id',
        ]);

        // Find the notification and mark it as read
        $notification = Notification::findOrFail($request->notification_id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        // Fetch the count of unread notifications for the authenticated user
        $count = Notification::where('is_read', false)
                             ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markAllAsRead()
    {
        // Fetch all unread notifications for the authenticated user and mark them as read
        Notification::where('is_read', false)
                    ->update(['is_read' => true]);

        // Optionally return the updated unread count
        $count = Notification::where('is_read', false)
                             ->count();

        return response()->json(['success' => true, 'unread_count' => $count]);
    }
}
