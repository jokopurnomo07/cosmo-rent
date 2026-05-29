<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingHistoryController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
 
        // Completed rentals (status: returned)
        $completedRentals = Rental::where('user_id', $userId)
            ->where('status', 'returned')
            ->with(['vehicle', 'rental_package'])
            ->latest('end_date')
            ->get();
 
        // Canceled or failed reservations
        $canceledReservations = Reservation::where('user_id', $userId)
            ->whereIn('status', ['canceled', 'failed'])
            ->with(['vehicle'])
            ->latest('updated_at')
            ->get();
 
        return view('user.history.index', compact('completedRentals', 'canceledReservations'));
    }
}
