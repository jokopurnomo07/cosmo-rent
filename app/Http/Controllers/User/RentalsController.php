<?php

namespace App\Http\Controllers\User;

use App\Models\Rental;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RentalsController extends Controller
{
    public function index(){

        $rentals = Rental::where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'DESC')
            ->latest()->paginate(10);
        $rentals->loadMissing([
            'user:id,name,email,phone,address',
            'vehicle',
            'rental_package',
        ]);
        $rentals->where('type', 'car')->load('services');
        $notifications = Notification::where('is_read', false)->latest()->paginate(10);

        return view('user.rentals.index', [
            'rentals' => $rentals,
            'notifications' => $notifications,
        ]);
    }

    public function show($id){
        $rentals = Rental::findOrFail($id);
        $rentals->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle', 'rental_package']);
        
        return view('user.rentals.show', compact('rentals'));
    }
}
