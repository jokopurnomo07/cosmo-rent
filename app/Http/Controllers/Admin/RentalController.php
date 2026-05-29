<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rental;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RentalController extends Controller
{
    public function index($status)
    {
        if ($status == "paid") {
            $status = ['paid'];
        } elseif ($status == "ongoing") {
            $status = ['ongoing'];
        } else {
            $status = ['returned'];
        }
        
        $rentals = Rental::with([
                'user:id,name,email,phone,address',
                'vehicle',
                'rental_package',
                'services',
            ])
            ->whereIn('status', $status)
            ->latest()
            ->paginate(10);

        $notifications = Notification::where('is_read', false)->latest()->paginate(10);

        return view('admin.rentals.index', [
            'rentals'       => $rentals,
            'notifications' => $notifications,
        ]);
    }

    public function show($id)
    {
        $rentals = Rental::findOrFail($id);
        $rentals->loadMissing([
            'user:id,name,email,phone,address',
            'services',
            'vehicle',
            'rental_package',
        ]);

        return view('admin.rentals.show', compact('rentals'));
    }

    public function updateStatus(Request $request)
    {
        $rental = Rental::findOrFail($request->id);
        $rental->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

        $rental->status = $request->status;

        if ($request->status === 'ongoing') {
            $rental->vehicle->status = 'rented';
            $rental->vehicle->save();
        } elseif ($request->status === 'returned') {
            $rental->vehicle->status = 'available';
            $rental->vehicle->save();
        }

        $rental->save();

        return response()->json(['success' => true]);
    }
}