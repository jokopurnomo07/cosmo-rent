<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehiclePrice;
use App\Models\RentalPackage;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VehiclePriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleAvanzaId = Vehicle::where('brand', 'Toyota')->first()->id;
        $vehicleBeatId = Vehicle::where('brand', 'Honda')->first()->id;

        // Isi data harga kendaraan
        VehiclePrice::create([
            'vehicle_id' => $vehicleAvanzaId,
            'price_4_hours' => 100000,
            'price_12_hours' => 150000,
            'price_24_hours' => 200000,
        ]);

        VehiclePrice::create([
            'vehicle_id' => $vehicleBeatId,
            'price_4_hours' => 50000,
            'price_12_hours' => 75000,
            'price_24_hours' => 100000,
        ]);
        VehiclePrice::create([
            'vehicle_id' => 3,
            'price_4_hours' => 50000,
            'price_12_hours' => 75000,
            'price_24_hours' => 100000,
        ]);
        VehiclePrice::create([
            'vehicle_id' => 4,
            'price_4_hours' => 50000,
            'price_12_hours' => 75000,
            'price_24_hours' => 100000,
        ]);
    }
}
