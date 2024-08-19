<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index($status){
        
        $reservation = Reservation::where('status', $status)->get();
        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

        return view('admin.reservation.index', [
            'reservation' => $reservation
        ]);
    }

    public function create(){
        return view('admin.reservation.create');
    }

    public function edit($id){
        return view('admin.reservation.edit');
    }
}
