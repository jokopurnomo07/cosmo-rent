<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rental;
use App\Models\RentalLocationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class RentalTrackingController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Map View for All Active Rentals
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $activeRentals = Rental::with(['user', 'vehicle'])
            ->whereIn('status', ['paid', 'ongoing'])
            ->get();

        // Get latest location for each rental
        $rentalLocations = [];
        foreach ($activeRentals as $rental) {
            $latestLocation = RentalLocationLog::getLatestForRental($rental->id);
            if ($latestLocation) {
                $rentalLocations[] = [
                    'rental_id' => $rental->id,
                    'vehicle_name' => $rental->vehicle->name,
                    'user_name' => $rental->user->name,
                    'latitude' => $latestLocation->latitude,
                    'longitude' => $latestLocation->longitude,
                    'address' => $latestLocation->address,
                    'updated_at' => $latestLocation->updated_at,
                ];
            }
        }

        return view('admin.tracking.index', [
            'activeRentals' => $activeRentals,
            'rentalLocations' => $rentalLocations,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Detailed Tracking for Single Rental
    // ─────────────────────────────────────────────────────────────────
    public function show($rentalId)
    {
        $rental = Rental::with(['user', 'vehicle'])
            ->findOrFail($rentalId);

        // Get last location
        $currentLocation = RentalLocationLog::getLatestForRental($rentalId);

        // Get location history
        $locationHistory = RentalLocationLog::getHistoryForRental($rentalId);

        // Only show tracking when rental is active, vehicle locked as rented,
        // or the rental period is currently ongoing (start_date..end_date)
        $showTracking = in_array($rental->status, ['paid', 'ongoing'])
            || ($rental->vehicle && $rental->vehicle->status === 'rented')
            || (
                $rental->start_date && $rental->end_date
                && now()->between($rental->start_date, $rental->end_date)
            );

        return view('admin.tracking.show', [
            'rental' => $rental,
            'currentLocation' => $currentLocation,
            'locationHistory' => $locationHistory,
            'showTracking' => $showTracking,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // API - Get Current Location for Rental (AJAX)
    // ─────────────────────────────────────────────────────────────────
    public function getCurrentLocation($rentalId)
    {
        try {
            $rental = Rental::findOrFail($rentalId);
            $allowed = in_array($rental->status, ['paid', 'ongoing'])
                || ($rental->vehicle && $rental->vehicle->status === 'rented')
                || (
                    $rental->start_date && $rental->end_date
                    && now()->between($rental->start_date, $rental->end_date)
                );
            if (! $allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking tidak tersedia untuk rental ini.',
                ]);
            }
            $location = RentalLocationLog::getLatestForRental($rentalId);

            // If there is no live location log yet, fall back to pickup coordinates
            if (!$location) {
                if ($rental->latitude && $rental->longitude) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'rental_id' => $rentalId,
                            'latitude' => $rental->latitude,
                            'longitude' => $rental->longitude,
                            'address' => $rental->address_pickup ?? null,
                            'speed' => 0,
                            'updated_at' => $rental->created_at,
                            'note' => 'fallback_pickup',
                        ],
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi belum tersedia untuk rental ini.',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rental_id' => $rentalId,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'address' => $location->address,
                    'speed' => $location->speed ?? 0,
                    'updated_at' => $location->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rental tidak ditemukan.',
            ], 404);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // API - Get Location History (AJAX)
    // ─────────────────────────────────────────────────────────────────
    public function getLocationHistory($rentalId)
    {
        try {
            $rental = Rental::findOrFail($rentalId);
            $allowed = in_array($rental->status, ['paid', 'ongoing'])
                || ($rental->vehicle && $rental->vehicle->status === 'rented')
                || (
                    $rental->start_date && $rental->end_date
                    && now()->between($rental->start_date, $rental->end_date)
                );
            if (! $allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking tidak tersedia untuk rental ini.',
                ]);
            }

            $locations = RentalLocationLog::getHistoryForRental($rentalId);

            // If no logs, include pickup point as initial history if available
            if ($locations->isEmpty() && $rental->latitude && $rental->longitude) {
                $pickup = collect([[
                    'latitude' => $rental->latitude,
                    'longitude' => $rental->longitude,
                    'address' => $rental->address_pickup ?? null,
                    'speed' => 0,
                    'created_at' => $rental->created_at,
                ]]);

                return response()->json([
                    'success' => true,
                    'data' => $pickup,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $locations->map(function ($log) {
                    return [
                        'latitude' => $log->latitude,
                        'longitude' => $log->longitude,
                        'address' => $log->address,
                        'speed' => $log->speed,
                        'created_at' => $log->created_at,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat lokasi.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // DEMO - Generate Random Location Updates (Simulate Movement)
    // ─────────────────────────────────────────────────────────────────
    public function generateDemoLocations($rentalId)
    {
        try {
            $rental = Rental::findOrFail($rentalId);

            // Use pickup location as starting point
            $baseLat = floatval($rental->latitude ?? -6.2088); // Jakarta default
            $baseLng = floatval($rental->longitude ?? 106.8456);

            // Generate 10 tracking points around the starting location
            // Radius: ~5km variation
            $numPoints = 10;
            $radiusInDegrees = 0.05; // ~5km
            $currentTime = now();

            for ($i = 0; $i < $numPoints; $i++) {
                // Random direction and distance
                $angle = (rand(0, 360) * M_PI) / 180;
                $distance = (rand(1, 100) / 100) * $radiusInDegrees;

                $lat = $baseLat + ($distance * cos($angle));
                $lng = $baseLng + ($distance * sin($angle));

                // Simulate speed (30-80 km/h)
                $speed = rand(30, 80);

                // Create location log with time progression
                RentalLocationLog::create([
                    'rental_id' => $rentalId,
                    'latitude' => number_format($lat, 6),
                    'longitude' => number_format($lng, 6),
                    'address' => $this->getAddressFromCoordinates($lat, $lng),
                    'speed' => $speed,
                    'created_at' => $currentTime->copy()->addMinutes($i * 10),
                    'updated_at' => $currentTime->copy()->addMinutes($i * 10),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Demo tracking data berhasil dibuat dengan ' . $numPoints . ' titik lokasi.',
                'count' => $numPoints,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate demo locations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat demo tracking: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // API - Add Tracking Point (Manual/Real-time)
    // ─────────────────────────────────────────────────────────────────
    public function addLocation(Request $request, $rentalId)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
            'speed' => 'nullable|integer',
        ]);

        try {
            $rental = Rental::findOrFail($rentalId);

            RentalLocationLog::create([
                'rental_id' => $rentalId,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'speed' => $request->speed,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi tracking berhasil ditambahkan.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan lokasi tracking.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // PUBLIC API - Add Tracking Point (from device/webhook)
    // Authenticated by TRACKING_SECRET via header 'X-TRACKING-TOKEN' or ?token=
    // ─────────────────────────────────────────────────────────────────
    public function addLocationPublic(Request $request, $rentalId)
    {
        $token = $request->header('X-TRACKING-TOKEN') ?? $request->query('token');
        if (! $token || $token !== env('TRACKING_SECRET')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
            'speed' => 'nullable|integer',
        ]);

        try {
            $rental = Rental::findOrFail($rentalId);

            RentalLocationLog::create([
                'rental_id' => $rentalId,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'speed' => $request->speed,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi tracking berhasil ditambahkan (public).',
            ]);

        } catch (\Exception $e) {
            Log::error('Public addLocation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan lokasi tracking.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper - Get Address from Coordinates (Dummy)
    // ─────────────────────────────────────────────────────────────────
    private function getAddressFromCoordinates($lat, $lng)
    {
        $address = "Lat: " . number_format($lat, 4) . ", Lng: " . number_format($lng, 4);
        return $address;
    }
}
