<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehiclePrice;
use Illuminate\Database\Seeder;

class VehiclePriceSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [
            'AB123CD' => ['price_4_hours' => 100000, 'price_12_hours' => 150000, 'price_24_hours' => 200000],
            'CD456EF' => ['price_4_hours' =>  50000, 'price_12_hours' =>  75000, 'price_24_hours' => 100000],
            'GH789IJ' => ['price_4_hours' =>  50000, 'price_12_hours' =>  75000, 'price_24_hours' => 100000],
            'JK012LM' => ['price_4_hours' =>  50000, 'price_12_hours' =>  75000, 'price_24_hours' => 100000],
        ];

        foreach ($prices as $regNumber => $priceData) {
            $vehicle = Vehicle::where('registration_number', $regNumber)->first();

            if (!$vehicle) {
                $this->command->warn("Vehicle dengan registration_number '{$regNumber}' tidak ditemukan. Dilewati.");
                continue;
            }

            VehiclePrice::create(array_merge(
                ['vehicle_id' => $vehicle->id],
                $priceData
            ));
        }
    }
}