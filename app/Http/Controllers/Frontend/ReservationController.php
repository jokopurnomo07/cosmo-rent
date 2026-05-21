<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\VehiclePrice;
use Illuminate\Http\Request;
use App\Models\RentalPackage;
use App\Mail\NotificationMail;
use App\Events\ReservationCreated;
use App\Models\ReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VehicleReservationConfirmation;
use App\Http\Requests\StoreReservationRequest;
use App\Mail\ReservationRejectionNotification;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        $vehicle = [];
        if ($request->id != null) {
            $vehicle = Vehicle::with(['features', 'prices'])->findOrFail($request->id);
        }

        $services       = Service::get();
        $rentalPackages = RentalPackage::get();
        $vehicles       = Vehicle::with(['features', 'prices'])->get();

        return view('frontend.pemesanan', [
            'vehicle'        => $vehicle,
            'services'       => $services,
            'vehicles'       => $vehicles,
            'rentalPackages' => $rentalPackages,
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        DB::beginTransaction();
        $rentalPackage = RentalPackage::findOrFail($request->rental_package_id);
        $vehiclePrice  = VehiclePrice::where('vehicle_id', $request->vehicle_id)
            ->select('id', 'price_' . $rentalPackage->duration_hours . '_hours')
            ->first();
        $totalPrice = $vehiclePrice->{'price_' . $rentalPackage->duration_hours . '_hours'};

        try {
            User::where('id', Auth::check() ? Auth::user()->id : null)
                ->update(['phone' => $request->no_hp_guest, 'address' => $request->address_pickup]);

            $reservation = Reservation::create([
                'user_id'           => Auth::check() ? Auth::user()->id : null,
                'vehicle_id'        => $request->vehicle_id,
                'rental_package_id' => $request->rental_package_id,
                'start_date'        => $request->start_rent,
                'end_date'          => $request->end_rent,
                'time_pickup'       => $request->time_pickup,
                'address_pickup'    => $request->address_pickup,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'total_price'       => $totalPrice,
                'trx_id'            => generateUniqueID(Reservation::class, 'trx_id'),
            ]);

            if ($request->type == 'car') {
                ReservationService::create([
                    'reservation_id' => $reservation->id,
                    'service_id'     => $request->service_id,
                ]);
            }

            $newReservation = Reservation::findOrFail($reservation->id);
            $newReservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);
            event(new ReservationCreated($newReservation));

            DB::commit();

            return redirect()->back()->with('success', true);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Reservation store failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal membuat reservasi. Silakan coba lagi.');
        }
    }

    public function searchVehicle(Request $request)
    {
        $searchTerm  = $request->input('q');
        $vehicleType = $request->input('vehicle_type');
        $query       = Vehicle::where('type', $vehicleType);

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $vehicles = $query->select('id', 'name')->limit(10)->get();

        $results = [];
        foreach ($vehicles as $vehicle) {
            $results[] = [
                'id'   => $vehicle->id,
                'text' => $vehicle->name,
            ];
        }

        return response()->json(['items' => $results]);
    }

    public function updateStatus($status, $id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$reservation) {
            return redirect()->route('home')->with('successCanceled', false);
        }

        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

        $reservation->status = $status;

        if ($status === 'canceled') {
            $reservation->reason_canceled = 'User telah membatalkan reservasi.';
            // Use a separate variable so $status (the route param) is not overwritten
            $emailLabel = 'Pembatalan';
            Mail::to($reservation->user->email)
                ->send(new ReservationRejectionNotification($reservation, $emailLabel));
        }

        $reservation->save();

        return redirect()->route('home')->with('successCanceled', true);
    }
}