<?php

namespace App\Events;

use App\Models\Notification;
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
        Notification::create([
            'type' => 'reservation.created',
            'data' => [
                'reservation_id' => $reservation->id,
                'trx_id' => $reservation->trx_id,
                'message' => 'New reservation created.',
                'created_at' => $reservation->created_at->toDateTimeString(),
            ],
            'is_read' => false, // Default to unread
        ]);
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
