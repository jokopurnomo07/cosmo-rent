<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $car1 = Vehicle::create([
            'name' => 'Toyota Camry',
            'type' => 'car',
            'brand' => 'Toyota',
            'model' => 'Camry',
            'year' => 2022,
            'transmission' => 'manual',
            'fuel' => 'Bensin',
            'registration_number' => 'AB123CD',
            'capacity' => 5,
            'description' => 'A comfortable car for family trips.',
            'status' => 'available',
            'vehicle_images' => 'vehicles/EF4p0MWzjdKOrZym8T7alVz9vhtD2w5Nkgpa95Xs.png',
        ]);

        $car2 = Vehicle::create([
            'name' => 'Honda Accord',
            'type' => 'car',
            'brand' => 'Honda',
            'model' => 'Accord',
            'year' => 2023,
            'transmission' => 'manual',
            'fuel' => 'Bensin',
            'registration_number' => 'CD456EF',
            'capacity' => 5,
            'description' => 'A sleek and stylish sedan.',
            'status' => 'available',
            'vehicle_images' => 'vehicles/xBckB7g70h5H46m6K7gTNLT2EwqeWg6hDL0QamEb.png',
        ]);

        // Attach features to cars
        $carFeatures = Feature::whereIn('name', ['pendingin_udara', 'gps', 'musik', 'bluetooth', 'kontrol_iklim', 'bagasi'])->get();
        $car1->features()->attach($carFeatures);
        $car2->features()->attach($carFeatures);

        // Seed Motorcycles
        $motorcycle1 = Vehicle::create([
            'name' => 'Yamaha R15',
            'type' => 'motorcycle',
            'brand' => 'Yamaha',
            'model' => 'R15',
            'year' => 2021,
            'transmission' => 'otomatic',
            'fuel' => 'Bensin',
            'registration_number' => 'GH789IJ',
            'capacity' => 2,
            'description' => 'A sporty motorcycle with great handling.',
            'status' => 'available',
            'vehicle_images' => 'vehicles/Q44674vRsxMoZIhbVLlih9xrAwHxVAd0Qp23TbFG.png',
        ]);

        $motorcycle2 = Vehicle::create([
            'name' => 'Honda CBR500R',
            'type' => 'motorcycle',
            'brand' => 'Honda',
            'model' => 'CBR500R',
            'year' => 2022,
            'transmission' => 'otomatic',
            'fuel' => 'Listrik',
            'registration_number' => 'JK012LM',
            'capacity' => 2,
            'description' => 'A versatile motorcycle for both city and highway riding.',
            'status' => 'available',
            'vehicle_images' => 'vehicles/Bzbm4wFzjNhZjFzffLtZTIyXVViRNuXXSaxhD9XQ.jpg',
        ]);

        // Attach features to motorcycles
        $motorcycleFeatures = Feature::whereIn('name', ['gps', 'musik', 'bluetooth', 'air_mineral', 'perjalanan_jangka_panjang', 'bagasi', 'helm', 'jas_hujan'])->get();
        $motorcycle1->features()->attach($motorcycleFeatures);
        $motorcycle2->features()->attach($motorcycleFeatures);

    }
}
