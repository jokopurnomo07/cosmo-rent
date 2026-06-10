<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ExtensionRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $extension;

    public function __construct($extension)
    {
        $this->extension = $extension;
    }

    public function broadcastOn()
    {
        return new Channel('extensions');
    }

    public function broadcastAs()
    {
        return 'extension.requested';
    }

    public function broadcastWith()
    {
        $rental = $this->extension->rental;

        return [
            'extension' => [
                'id' => $this->extension->id,
                'rental_id' => $rental->id ?? null,
                'trx_id' => $rental->trx_id ?? null,
                'vehicle_name' => $rental->vehicle->name ?? null,
                'user_name' => $rental->user->name ?? null,
                'created_at' => $this->extension->created_at->toDateTimeString(),
            ]
        ];
    }
}
