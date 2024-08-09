<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservation1Id = Reservation::where('status', 'confirmed')->first()->id;
        $reservation2Id = Reservation::where('status', 'pending')->first()->id;

        // Isi data pembayaran
        Payment::create([
            'reservation_id' => $reservation1Id,
            'amount' => 200000,
            'payment_method' => 'direct', // atau 'xendit'
            'status' => 'completed',
        ]);

        Payment::create([
            'reservation_id' => $reservation2Id,
            'amount' => 100000,
            'payment_method' => 'xendit',
            'status' => 'pending',
        ]);
    }
}
