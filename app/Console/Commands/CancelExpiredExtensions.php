<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RentalExtension;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class CancelExpiredExtensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel rental extensions that were approved but not paid within the allowed time (24h)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = RentalExtension::where('status', 'approved')
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        foreach ($expired as $extension) {
            try {
                $extension->status = 'canceled';
                $extension->reason_rejected = 'Pembayaran tidak diterima dalam 24 jam';
                $extension->save();

                // Notify user
                if ($extension->rental && $extension->rental->user_id) {
                    Notification::create([
                        'user_id' => $extension->rental->user_id,
                        'type' => 'extension_canceled',
                        'title' => 'Perpanjangan Dibatalkan',
                        'message' => "Perpanjangan untuk {$extension->rental->vehicle->name} dibatalkan karena pembayaran tidak diterima dalam 24 jam.",
                        'data' => json_encode(['extension_id' => $extension->id, 'rental_id' => $extension->rental_id]),
                        'is_read' => false,
                    ]);
                }

                Log::info('Canceled expired extension: ' . $extension->id);
            } catch (\Exception $e) {
                Log::error('Failed to cancel expired extension ' . $extension->id . ': ' . $e->getMessage());
            }
        }

        $this->info('Processed ' . $expired->count() . ' expired extensions.');

        return 0;
    }
}
