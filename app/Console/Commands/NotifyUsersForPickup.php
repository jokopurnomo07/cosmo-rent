<?php

namespace App\Console\Commands;

use App\Models\Rental;
use Illuminate\Console\Command;
use App\Mail\VehicleReadyForPickup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyUsersForPickup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:pickup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $rentals = Rental::whereDate('start_date', $today)
            ->with(['user:id,name,email,phone,address', 'services', 'vehicle'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($rentals as $rental) {
            $recipientEmail = $rental->user_id !== null
                ? $rental->user?->email
                : $rental->email_guest;

            if (empty($recipientEmail)) {
                Log::warning('Pickup notification skipped: no recipient email', ['rental_id' => $rental->id]);
                $failed++;
                continue;
            }

            try {
                Mail::to($recipientEmail)->send(new VehicleReadyForPickup($rental));
                $sent++;
            } catch (\Exception $e) {
                Log::error('Pickup notification failed: ' . $e->getMessage(), ['rental_id' => $rental->id]);
                $failed++;
            }
        }

        $this->info("Notifications: {$sent} sent, {$failed} failed.");
    }
}
