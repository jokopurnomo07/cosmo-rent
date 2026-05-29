<?php

namespace App\Http\Controllers\User;

use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Reservation::with(['vehicle', 'rental_package', 'services', 'user'])
            ->where('user_id', auth()->id())
            ->latest();

        if ($status === 'pending') {
            $query->whereIn('status', ['pending']);
        } elseif ($status === 'canceled') {
            $query->whereIn('status', ['canceled', 'rejected']);
        } else {
            $query->whereIn('status', ['pending', 'confirmed', 'paid', 'failed']);
        }

        $reservations = $query->paginate(10);

        $notifications = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->get();

        return view('user.reservation.index', compact('reservations', 'notifications'));
    }

    public function show($id)
    {
        $rentals = Reservation::with([
            'user:id,name,email,phone,address',
            'services',
            'vehicle',
            'rental_package'
        ])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('user.reservation.show', compact('rentals'));
    }
}