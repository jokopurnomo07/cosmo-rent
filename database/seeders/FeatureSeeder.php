<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['name' => 'pendingin_udara', 'type' => 'car'],
            ['name' => 'kursi_anak', 'type' => 'car'],
            ['name' => 'gps', 'type' => 'both'],
            ['name' => 'bagasi', 'type' => 'car'],
            ['name' => 'musik', 'type' => 'both'],
            ['name' => 'sabuk_pengaman', 'type' => 'car'],
            ['name' => 'tempat_tidur', 'type' => 'motorcycle'],
            ['name' => 'air_mineral', 'type' => 'both'],
            ['name' => 'bluetooth', 'type' => 'both'],
            ['name' => 'audio_input', 'type' => 'both'],
            ['name' => 'perjalanan_jangka_panjang', 'type' => 'both'],
            ['name' => 'car_kit', 'type' => 'car'],
            ['name' => 'penguncian_sentral_jarak_auh', 'type' => 'car'],
            ['name' => 'kontrol_iklim', 'type' => 'car'],
            ['name' => 'helm', 'type' => 'motorcycle'],
            ['name' => 'jas_hujan', 'type' => 'motorcycle'],
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }
    }
}
