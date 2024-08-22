<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Rental;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index(){
        $totalVehicle = Vehicle::count();
        $totalRent = Rental::where('status', 'returned')->count();
        $totalReservation = Reservation::where('status', 'confirmed')->count();
        $activityLog = ActivityLog::with('user')->get();
        
        return view('admin.dashboard', [
            'totalVehicle' => $totalVehicle,
            'totalRent' => $totalRent,
            'totalReservation' => $totalReservation,
            'activityLog' => $activityLog,
        ]);
    }

    public function indexUser(){
        return view('user.dashboard');
    }
}
