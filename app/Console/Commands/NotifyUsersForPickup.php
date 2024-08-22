<?php

namespace App\Console\Commands;

use App\Models\Rental;
use Illuminate\Console\Command;
use App\Mail\VehicleReadyForPickup;
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
        $today = date('Y-m-d');
        $rentals = Rental::whereDate('start_date', $today)->get();
        $rentals->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

        foreach ($rentals as $rental) {
            $recipientEmail = $rental->user_id != null ? $rental->user->email : $rental->email_guest;
        
            Mail::to($recipientEmail)->send(new VehicleReadyForPickup($rental));
        }

        $this->info('notifications sent successfully!');
    }
}
