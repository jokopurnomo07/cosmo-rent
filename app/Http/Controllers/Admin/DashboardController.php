<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rental;
use App\Models\Vehicle;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\RentalExtension;


class DashboardController extends Controller
{
    public function index(){
        $totalVehicle = Cache::remember('total_vehicle', 600, fn() => Vehicle::count());
        $totalRent = Cache::remember('total_rent', 600, fn() => Rental::where('status', 'returned')->count());
        $totalReservation = Cache::remember('total_reservation', 600, fn() => Reservation::where('status', 'confirmed')->count());
        $activityLog = ActivityLog::with('user')->latest()->paginate(10);
        $notifications = Notification::where('is_read', false)->latest()->paginate(10);
        
        return view('admin.dashboard', [
            'totalVehicle' => $totalVehicle,
            'totalRent' => $totalRent,
            'totalReservation' => $totalReservation,
            'activityLog' => $activityLog,
            'notifications' => $notifications,
        ]);
    }

    public function indexUser(){
        $userId = auth()->id();

        // Penyewaan selesai milik user
        $totalRent = Rental::where('user_id', $userId)
            ->whereIn('status', ['completed', 'returned'])
            ->count();

        // Reservasi confirmed milik user
        $totalReservation = Reservation::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->count();

        // Penyewaan sedang aktif (ambil semua, user bisa punya >1)
        $activeRentals = Rental::where('user_id', $userId)
            ->whereIn('status', ['ongoing', 'paid', 'awaiting_confirmation'])
            ->with('vehicle')
            ->latest()
            ->get();

        $activeCount = $activeRentals->count();

        // 5 riwayat sewa terbaru
        $recentRentals = Rental::where('user_id', $userId)
            ->with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        // Notifikasi — perlu konfirmasi struktur tabel notifications
        $notifications = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->latest()
            ->paginate(10);

        // Extension stats for user
        $extensionCounts = [
            'pending'  => RentalExtension::whereHas('rental', fn($q) => $q->where('user_id', $userId))->where('status', 'pending')->count(),
            'approved' => RentalExtension::whereHas('rental', fn($q) => $q->where('user_id', $userId))->where('status', 'approved')->count(),
            'paid'     => RentalExtension::whereHas('rental', fn($q) => $q->where('user_id', $userId))->where('status', 'paid')->count(),
        ];

        $pendingExtensions = RentalExtension::with('rental.vehicle')
            ->whereHas('rental', fn($q) => $q->where('user_id', $userId))
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('user.dashboard', [
            'totalRent'        => $totalRent,
            'totalReservation' => $totalReservation,
            'activeRentals'    => $activeRentals,
            'activeCount'      => $activeCount,
            'recentRentals'    => $recentRentals,
            'notifications'    => $notifications,
            'extensionCounts'  => $extensionCounts,
            'pendingExtensions'=> $pendingExtensions,
        ]);
    }
}
