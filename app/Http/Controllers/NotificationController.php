<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // GET UNREAD COUNT
    // ─────────────────────────────────────────────────────────────────
    public function getUnreadCount()
    {
        $count = Notification::unread()
            ->forUser(auth()->id())
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // ─────────────────────────────────────────────────────────────────
    // MARK AS READ
    // ─────────────────────────────────────────────────────────────────
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id',
        ]);

        $notification = Notification::where('id', $request->notification_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'success'      => true,
            'is_read'      => true,
            'unread_count' => Notification::unread()->forUser(auth()->id())->count(),
            'url'          => $this->resolveUrl($notification),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // MARK AS UNREAD
    // ─────────────────────────────────────────────────────────────────
    public function markAsUnread(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id',
        ]);

        $notification = Notification::where('id', $request->notification_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->update(['is_read' => false]);

        return response()->json([
            'success'      => true,
            'is_read'      => false,
            'unread_count' => Notification::unread()->forUser(auth()->id())->count(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // TOGGLE READ / UNREAD
    // Satu endpoint untuk handle checkbox toggle dari frontend
    // ─────────────────────────────────────────────────────────────────
    public function toggleRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id',
        ]);

        $notification = Notification::where('id', $request->notification_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Flip status
        $newStatus = ! $notification->is_read;
        $notification->update(['is_read' => $newStatus]);

        return response()->json([
            'success'      => true,
            'is_read'      => $newStatus,
            'unread_count' => Notification::unread()->forUser(auth()->id())->count(),
            'url'          => $newStatus ? $this->resolveUrl($notification) : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // MARK ALL AS READ
    // ─────────────────────────────────────────────────────────────────
    public function markAllAsRead()
    {
        Notification::unread()
            ->forUser(auth()->id())
            ->update(['is_read' => (int) 1]);

        return response()->json(['success' => true, 'unread_count' => 0]);
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE: Resolve redirect URL by notification type
    // ─────────────────────────────────────────────────────────────────
    private function resolveUrl(Notification $notification): string
    {
        return match ($notification->type) {
            'new_reservation'       => auth()->user()->hasRole('admin')
                                        ? route('admin.reservations.index', 'pending')
                                        : route('user.reservations.index'),
            'reservation_confirmed',
            'reservation_rejected',
            'reservation_canceled'  => route('user.reservations.index'),
            'reservation_paid'      => route('admin.reservations.index', 'confirmed'),
            default                 => route('home'),
        };
    }
}