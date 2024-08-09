<?php

namespace Database\Seeders;

use App\Models\RentalPackage;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RentalPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RentalPackage::create([
            'name' => '4 Jam',
            'duration_hours' => 4,
            'description' => 'Paket sewa kendaraan untuk 6 jam.',
        ]);

        RentalPackage::create([
            'name' => '12 Jam',
            'duration_hours' => 12,
            'description' => 'Paket sewa kendaraan untuk 12 jam.',
        ]);

        RentalPackage::create([
            'name' => '24 Jam',
            'duration_hours' => 24,
            'description' => 'Paket sewa kendaraan untuk 1 hari (24 jam).',
        ]);
    }
}
