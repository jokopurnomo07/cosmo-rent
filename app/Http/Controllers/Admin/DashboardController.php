<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index(){
        $totalVehicle = Vehicle::count();
        $totalRent = Rental::where('status', 'returned')->count();
        $totalReservation = Reservation::where('status', 'confirmed')->count();
        
        return view('admin.dashboard', [
            'totalVehicle' => $totalVehicle,
            'totalRent' => $totalRent,
            'totalReservation' => $totalReservation,
        ]);
    }

    public function indexUser(){
        return view('user.dashboard');
    }
}
