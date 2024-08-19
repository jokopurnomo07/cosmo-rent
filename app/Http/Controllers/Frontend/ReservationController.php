<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\RentalPackage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreReservationRequest;
use App\Models\ReservationService;

class ReservationController extends Controller
{
    public function create(Request $request){

        $vehicle = [];
        if( $request->id != null ){
            $vehicle = Vehicle::with(['features','prices'])->findOrFail($request->id);
        }

        $services = Service::all();
        $rentalPackages = RentalPackage::all();
        $vehicles = Vehicle::with(['features','prices'])->get();

        return view('frontend.pemesanan', [
            'vehicle' => $vehicle,
            'services' => $services,
            'vehicles' => $vehicles,
            'rentalPackages' => $rentalPackages,
        ]);
    }

    public function store(StoreReservationRequest $request){
        
        DB::beginTransaction();
        try{
            $reservation = Reservation::create([
                'user_id' => Auth::check() ? Auth::user()->id : null,
                'vehicle_id' => $request->vehicle_id,
                'rental_package_id' => $request->rental_package_id,
                'start_date' => $request->start_rent,
                'end_date' => $request->end_rent,
                'time_pickup' => $request->time_pickup,
                'nama_guest' => $request->nama_guest,
                'email_guest' => $request->email_guest,
                'no_hp_guest' => $request->no_hp_guest,
                'address_pickup' => $request->address_pickup,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if( $request->type == 'car' ){
                $service = ReservationService::create([
                    'reservation_id' => $reservation->id,
                    'service_id' => $request->service_id,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', true);

        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add reservation. Please try again.');
        }
    }
}
