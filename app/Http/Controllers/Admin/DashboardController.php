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
        $totalVehicle = Cache::remember('total_vehicle', 600, fn() => Vehicle::count());
        $totalRent = Cache::remember('total_rent', 600, fn() => Rental::where('status', 'returned')->count());
        $totalReservation = Cache::remember('total_reservation', 600, fn() => Reservation::where('status', 'confirmed')->count());
        $activityLog = ActivityLog::with('user')->where('user_id', auth()->user()->id)->latest()->paginate(10);
        $notifications = Notification::where('is_read', false)->latest()->paginate(10);
        
        return view('user.dashboard', [
            'totalVehicle' => $totalVehicle,
            'totalRent' => $totalRent,
            'totalReservation' => $totalReservation,
            'activityLog' => $activityLog,
            'notifications' => $notifications,
        ]);
    }
}
