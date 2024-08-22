<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rental;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RentalController extends Controller
{
    public function index($status){
        if( $status == "paid" ){
            $status = ['paid'];
        }elseif( $status == "ongoing" ){
            $status = ['ongoing'];
        }else{
            $status = ['returned'];
        }

        $rentals = Rental::whereIn('status', $status)->orderBy('created_at', 'DESC')->get();
        $rentals->loadMissing([
            'user:id,name,email,phone,address',
            'vehicle',
            'rental_package',
        ]);
        $rentals->where('type', 'car')->load('services');
        

        return view('admin.rentals.index', [
            'rentals' => $rentals,
        ]);
    }

    public function updateStatus(Request $request){
        $reservation = Rental::find($request->id);
        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);
        if ($reservation) {
            $reservation->status = $request->status;
            $reservation->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
