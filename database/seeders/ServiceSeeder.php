<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create([
            'name' => 'Antar Jemput Bandara',
            'description' => 'Layanan antar jemput ke dan dari bandara.',
        ]);

        Service::create([
            'name' => 'Upacara Pernikahan',
            'description' => 'Layanan kendaraan untuk upacara pernikahan.',
        ]);

        Service::create([
            'name' => 'Antar Jemput Kota',
            'description' => 'Layanan antar jemput ke berbagai lokasi dalam kota.',
        ]);

        Service::create([
            'name' => 'Tur Seluruh Kota',
            'description' => 'Layanan tur mengelilingi seluruh kota.',
        ]);
    }
}
