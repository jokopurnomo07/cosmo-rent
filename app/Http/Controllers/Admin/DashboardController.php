<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rental;
use App\Models\Vehicle;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(){
        $totalVehicle = Vehicle::count();
        $totalRent = Rental::where('status', 'returned')->count();
        $totalReservation = Reservation::where('status', 'confirmed')->count();
        $activityLog = ActivityLog::with('user')->get();
        $notifications = Notification::where('is_read', false)->get();
        
        return view('admin.dashboard', [
            'totalVehicle' => $totalVehicle,
            'totalRent' => $totalRent,
            'totalReservation' => $totalReservation,
            'activityLog' => $activityLog,
            'notifications' => $notifications,
        ]);
    }

    public function indexUser(){
        return view('user.dashboard');
    }
}
