<?php

namespace App\Http\Controllers\User;

use App\Models\Rental;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RentalsController extends Controller
{
    public function index()
    {
        $rentals = Rental::where('user_id', auth()->user()->id)
            ->latest()
            ->paginate(10);

        $rentals->loadMissing([
            'user:id,name,email,phone,address',
            'vehicle',
            'rental_package',
        ]);

        $rentals->getCollection()
            ->filter(fn($r) => $r->vehicle && $r->vehicle->type === 'car')
            ->each(fn($r) => $r->loadMissing('services'));

        $notifications = Notification::where('is_read', false)->latest()->paginate(10);

        return view('user.rentals.index', [
            'rentals'       => $rentals,
            'notifications' => $notifications,
        ]);
    }

    public function show($id)
    {
        $rentals = Rental::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $rentals->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle', 'rental_package']);

        return view('user.rentals.show', compact('rentals'));
    }
}
