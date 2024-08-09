<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Reservation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleAvanzaId = Vehicle::where('brand', 'Toyota')->first()->id;
        $vehicleBeatId = Vehicle::where('brand', 'Honda')->first()->id;
        $user1Id = User::where('email', 'john.doe@example.com')->first()->id;
        $user2Id = User::where('email', 'jane.smith@example.com')->first()->id;

        // Isi data reservasi
        Reservation::create([
            'user_id' => $user1Id,
            'vehicle_id' => $vehicleAvanzaId,
            'rental_package_id' => 1,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(2),
            'total_price' => 200000,
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $user2Id,
            'vehicle_id' => $vehicleBeatId,
            'rental_package_id' => 1,
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(4),
            'total_price' => 100000,
            'status' => 'pending',
        ]);
    }
}
