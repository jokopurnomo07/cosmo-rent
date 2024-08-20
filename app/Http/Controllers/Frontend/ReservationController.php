<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\VehiclePrice;
use Illuminate\Http\Request;
use App\Models\RentalPackage;
use App\Models\ReservationService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreReservationRequest;

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
        $rentalPackage = RentalPackage::findOrFail($request->rental_package_id);
        $vehiclePrice = VehiclePrice::where('vehicle_id', $request->vehicle_id)->select('id', 'price_' . $rentalPackage->duration_hours . '_hours')->first();
        $totalPrice = $vehiclePrice->{'price_' . $rentalPackage->duration_hours . '_hours'};

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
                'total_price' => $totalPrice,
                'trx_id' => generateUniqueID(Reservation::class, 'trx_id'),
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

    public function searchVehicle(Request $request){

        $searchTerm = $request->input('q');
        $vehicleType = $request->input('vehicle_type');
        $query = Vehicle::where('type', $vehicleType);

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $vehicles = $query->select('id', 'name')->limit(10)->get();

        $results = [];
        foreach ($vehicles as $vehicle) {
            $results[] = [
                'id' => $vehicle->id, // The ID of the vehicle
                'text' => $vehicle->name // The text that will be displayed in the dropdown
            ];
        }

        return response()->json(['items' => $results]);
    }
}
