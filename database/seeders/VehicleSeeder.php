<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class VehicleSeeder extends Seeder
{
    /**
     * Buat placeholder image dengan GD
     * Warna dan label berbeda tiap kendaraan
     */
    private function createPlaceholderImage(string $filename, string $label, string $bgColor = '4A90D9'): void
    {
        $path = storage_path('app/public/vehicles/');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $fullPath = $path . $filename;

        if (file_exists($fullPath)) {
            return; // Skip jika sudah ada
        }

        $width  = 800;
        $height = 500;
        $image  = imagecreatetruecolor($width, $height);

        // Parse hex color untuk background
        $r = hexdec(substr($bgColor, 0, 2));
        $g = hexdec(substr($bgColor, 2, 2));
        $b = hexdec(substr($bgColor, 4, 2));

        $bg      = imagecolorallocate($image, $r, $g, $b);
        $white   = imagecolorallocate($image, 255, 255, 255);
        $overlay = imagecolorallocatealpha($image, 0, 0, 0, 60);

        imagefill($image, 0, 0, $bg);

        // Overlay gelap di bawah untuk teks
        imagefilledrectangle($image, 0, $height - 80, $width, $height, $overlay);

        // Tulis label kendaraan
        $fontSize = 5;
        $textWidth  = imagefontwidth($fontSize) * strlen($label);
        $textX = ($width - $textWidth) / 2;
        imagestring($image, $fontSize, $textX, $height - 55, $label, $white);

        // Simpan
        imagejpeg($image, $fullPath, 90);
        imagedestroy($image);
    }

    public function run(): void
    {
        // Buat placeholder dengan warna berbeda tiap kendaraan
        $placeholders = [
            'toyota_camry.jpg'  => ['Toyota Camry 2022',  '2C5F8A'],
            'honda_accord.jpg'  => ['Honda Accord 2023',  '8A2C2C'],
            'yamaha_r15.jpg'    => ['Yamaha R15 2021',    '2C8A4A'],
            'honda_cbr500r.jpg' => ['Honda CBR500R 2022', '5A2C8A'],
        ];

        foreach ($placeholders as $filename => [$label, $color]) {
            $this->createPlaceholderImage($filename, $label, $color);
            $this->command->info("Placeholder dibuat: vehicles/{$filename}");
        }

        // Cars
        $car1 = Vehicle::create([
            'name'                => 'Toyota Camry',
            'type'                => 'car',
            'brand'               => 'Toyota',
            'model'               => 'Camry',
            'year'                => 2022,
            'transmission'        => 'manual',
            'fuel'                => 'Bensin',
            'registration_number' => 'AB123CD',
            'capacity'            => 5,
            'description'         => 'Mobil keluarga yang nyaman untuk perjalanan jauh.',
            'status'              => 'available',
            'vehicle_images'      => 'vehicles/toyota_camry.jpg',
        ]);

        $car2 = Vehicle::create([
            'name'                => 'Honda Accord',
            'type'                => 'car',
            'brand'               => 'Honda',
            'model'               => 'Accord',
            'year'                => 2023,
            'transmission'        => 'manual',
            'fuel'                => 'Bensin',
            'registration_number' => 'CD456EF',
            'capacity'            => 5,
            'description'         => 'Sedan elegan dengan performa tinggi.',
            'status'              => 'available',
            'vehicle_images'      => 'vehicles/honda_accord.jpg',
        ]);

        $carFeatures = Feature::whereIn('name', [
            'pendingin_udara', 'gps', 'musik', 'bluetooth', 'kontrol_iklim', 'bagasi'
        ])->get();

        $car1->features()->attach($carFeatures);
        $car2->features()->attach($carFeatures);

        // Motorcycles
        $motorcycle1 = Vehicle::create([
            'name'                => 'Yamaha R15',
            'type'                => 'motorcycle',
            'brand'               => 'Yamaha',
            'model'               => 'R15',
            'year'                => 2021,
            'transmission'        => 'automatic',
            'fuel'                => 'Bensin',
            'registration_number' => 'GH789IJ',
            'capacity'            => 2,
            'description'         => 'Motor sport dengan handling yang responsif.',
            'status'              => 'available',
            'vehicle_images'      => 'vehicles/yamaha_r15.jpg',
        ]);

        $motorcycle2 = Vehicle::create([
            'name'                => 'Honda CBR500R',
            'type'                => 'motorcycle',
            'brand'               => 'Honda',
            'model'               => 'CBR500R',
            'year'                => 2022,
            'transmission'        => 'automatic',
            'fuel'                => 'Bensin',
            'registration_number' => 'JK012LM',
            'capacity'            => 2,
            'description'         => 'Motor serba bisa untuk kota maupun jalan raya.',
            'status'              => 'available',
            'vehicle_images'      => 'vehicles/honda_cbr500r.jpg',
        ]);

        $motorcycleFeatures = Feature::whereIn('name', [
            'gps', 'musik', 'bluetooth', 'air_mineral',
            'perjalanan_jangka_panjang', 'bagasi', 'helm', 'jas_hujan'
        ])->get();

        $motorcycle1->features()->attach($motorcycleFeatures);
        $motorcycle2->features()->attach($motorcycleFeatures);
    }
}