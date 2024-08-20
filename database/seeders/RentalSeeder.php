<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Rental;
use App\Models\Vehicle;
use App\Models\RentalPackage;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RentalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packageId = RentalPackage::first()->id;
        $vehicleAvanzaId = Vehicle::where('brand', 'Toyota')->first()->id;
        $vehicleBeatId = Vehicle::where('brand', 'Honda')->first()->id;
        $user1Id = User::where('email', 'john.doe@example.com')->first()->id;
        $user2Id = User::where('email', 'jane.smith@example.com')->first()->id;

        Rental::create([
            'trx_id' => uniqid(),
            'user_id' => $user1Id,
            'vehicle_id' => $vehicleAvanzaId,
            'rental_package_id' => $packageId,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addHours(6),
            'total_price' => 100000,
            'status' => 'ongoing',
        ]);

        Rental::create([
            'trx_id' => uniqid(),
            'user_id' => $user2Id,
            'vehicle_id' => $vehicleBeatId,
            'rental_package_id' => $packageId,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addHours(12),
            'total_price' => 150000,
            'status' => 'ongoing',
        ]);
    }
}
