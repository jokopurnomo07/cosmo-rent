<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;

    public function __construct($reservation)
    {
        $this->reservation = $reservation;
        $admins = User::role('admin')->pluck('id');

        if ($admins->isEmpty()) {
            return;
        }

        $now = now();

        Notification::insert(
            $admins->map(fn($adminId) => [
                'user_id'    => $adminId,
                'type'       => 'reservation.created',
                'data'       => json_encode([
                    'reservation_id' => $reservation->id,
                    'trx_id'         => $reservation->trx_id,
                    'message'        => 'New reservation created.',
                    'created_at'     => $reservation->created_at->toDateTimeString(),
                    'user_id'        => $reservation->user_id,
                ]),
                'is_read'    => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray()
        );
    }

    public function broadcastOn()
    {
        return new Channel('reservations');
    }

    public function broadcastAs()
    {
        return 'reservation.created';
    }
}
