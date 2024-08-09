<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Review;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReviewSeeder extends Seeder
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

        // Isi data ulasan
        Review::create([
            'vehicle_id' => $vehicleAvanzaId,
            'user_id' => $user1Id,
            'rating' => 4,
            'comment' => 'Kendaraan sangat nyaman dan bersih.',
        ]);

        Review::create([
            'vehicle_id' => $vehicleBeatId,
            'user_id' => $user2Id,
            'rating' => 5,
            'comment' => 'Performanya sangat baik, sangat puas!',
        ]);
    }
}
