<?php

namespace App\Http\Controllers\User;

use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['vehicle', 'rental_package', 'services'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        $notifications = Notification::where('is_read', false)->latest()->paginate(10);

        return view('user.reservation.index', compact('reservations', 'notifications'));
    }

    public function show($id)
    {
        $reservation = Reservation::with(['user:id,name,email,phone,address', 'services', 'vehicle', 'rental_package'])
            ->findOrFail($id);

        return view('user.reservation.show', compact('reservation'));
    }
}