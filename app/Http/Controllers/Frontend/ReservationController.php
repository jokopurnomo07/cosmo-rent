<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\Notification;
use App\Models\VehiclePrice;
use Illuminate\Http\Request;
use App\Models\RentalPackage;
use App\Events\ReservationCreated;
use App\Models\ReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VehicleReservationConfirmation;
use App\Mail\ReservationRejectionNotification;
use Carbon\Carbon;

class ReservationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // CREATE FORM
    // ─────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $vehicle = [];
        if ($request->id != null) {
            $vehicle = Vehicle::with(['features', 'prices'])->findOrFail($request->id);
        }

        $services       = Service::get();
        $rentalPackages = RentalPackage::get();

        $vehicles = Vehicle::with(['features', 'prices'])
            ->where('status', '!=', 'rented')
            ->get();

        return view('frontend.pemesanan', [
            'vehicle'        => $vehicle,
            'services'       => $services,
            'vehicles'       => $vehicles,
            'rentalPackages' => $rentalPackages,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // AJAX: Search vehicles
    // ─────────────────────────────────────────────────────────────────
    public function searchVehicle(Request $request)
    {
        $searchTerm  = $request->input('q');
        $vehicleType = $request->input('vehicle_type');

        $query = Vehicle::where('type', $vehicleType)
            ->where('status', '!=', 'rented');

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

    // ─────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'type'              => 'required|in:motorcycle,car',
            'service_id'        => 'required_if:type,car|nullable|exists:services,id',
            'rental_package_id' => 'required|exists:rental_packages,id',
            'start_rent'        => 'required|date|after_or_equal:today',
            'time_pickup'       => 'required|date_format:H:i',
            'vehicle_id'        => 'required|exists:vehicles,id',
            'address_pickup'    => 'required|string|min:10|max:255',
            'no_hp_guest'       => 'required|string|max:20',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
        ], [
            'type.required'              => 'Tipe kendaraan harus dipilih.',
            'type.in'                    => 'Tipe kendaraan tidak valid.',
            'service_id.required_if'     => 'Layanan harus dipilih untuk kendaraan mobil.',
            'service_id.exists'          => 'Layanan yang dipilih tidak valid.',
            'rental_package_id.required' => 'Paket sewa harus dipilih.',
            'rental_package_id.exists'   => 'Paket sewa yang dipilih tidak valid.',
            'start_rent.required'        => 'Tanggal mulai sewa harus dipilih.',
            'start_rent.date'            => 'Format tanggal mulai tidak valid.',
            'start_rent.after_or_equal'  => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'time_pickup.required'       => 'Waktu pengambilan harus dipilih.',
            'time_pickup.date_format'    => 'Format waktu pengambilan tidak valid (gunakan HH:mm).',
            'vehicle_id.required'        => 'Kendaraan harus dipilih.',
            'vehicle_id.exists'          => 'Kendaraan yang dipilih tidak valid.',
            'address_pickup.required'    => 'Alamat penjemputan harus diisi.',
            'address_pickup.min'         => 'Alamat penjemputan terlalu singkat (min 10 karakter).',
            'no_hp_guest.required'       => 'Nomor telepon harus diisi.',
        ]);

        try {
            DB::beginTransaction();

            $rentalPackage = RentalPackage::findOrFail($request->rental_package_id);
            $startDate     = Carbon::parse($request->start_rent . ' ' . $request->time_pickup);
            $endDate       = $startDate->copy()->addHours($rentalPackage->duration_hours);

            $priceColumn  = 'price_' . $rentalPackage->duration_hours . '_hours';
            $vehiclePrice = VehiclePrice::where('vehicle_id', $request->vehicle_id)
                ->select('id', $priceColumn)
                ->firstOrFail();

            $totalPrice = $vehiclePrice->{$priceColumn};

            // Update profil user — bypass fillable dengan direct assignment
            $user          = Auth::user();
            $user->phone   = $request->no_hp_guest;
            $user->address = $request->address_pickup;
            $user->save();

            $reservation = Reservation::create([
                'user_id'           => Auth::id(),
                'vehicle_id'        => $request->vehicle_id,
                'rental_package_id' => $request->rental_package_id,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'time_pickup'       => $request->time_pickup,
                'address_pickup'    => $request->address_pickup,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'total_price'       => $totalPrice,
                'trx_id'            => generateUniqueID(Reservation::class, 'trx_id'),
                'status'            => 'pending',
            ]);

            if ($request->type == 'car' && $request->service_id) {
                ReservationService::create([
                    'reservation_id' => $reservation->id,
                    'service_id'     => $request->service_id,
                ]);
            }

            // ── COMMIT dulu, baru dispatch event ─────────────────────
            DB::commit();

            try {
                $newReservation = Reservation::with([
                    'user:id,name,email,phone,address',
                    'services',
                    'vehicle',
                ])->findOrFail($reservation->id);

                event(new ReservationCreated($newReservation));

            } catch (\Exception $eventException) {
                Log::error('ReservationCreated event failed: ' . $eventException->getMessage(), [
                    'reservation_id' => $reservation->id,
                    'trx_id'         => $reservation->trx_id,
                ]);
            }

            return redirect()->back()->with('success', true);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation store failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Gagal membuat reservasi. Silakan coba lagi.');
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE STATUS (user cancel)
    // ─────────────────────────────────────────────────────────────────
    public function updateStatus($status, $id)
    {
        if (! in_array($status, ['canceled'])) {
            abort(403);
        }

        $reservation = Reservation::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $reservation) {
            return redirect()->route('home')->with('successCanceled', false);
        }

        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);
        $reservation->status          = 'canceled';
        $reservation->reason_canceled = 'User telah membatalkan reservasi.';
        $reservation->save();

        // ── Email konfirmasi pembatalan ke user ───────────────────────
        try {
            Mail::to($reservation->user->email)
                ->send(new ReservationRejectionNotification($reservation, 'Pembatalan'));
        } catch (\Exception $e) {
            Log::error('Cancel email failed: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
            ]);
        }

        // ── Notifikasi in-app ke semua admin ──────────────────────────
        try {
            $userName = $reservation->user?->name ?? 'User';
            $admins   = User::role('admin')->get(['id']);

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type'    => 'reservation_canceled',
                    'data'    => [
                        'title'          => 'Reservasi Dibatalkan oleh User',
                        'message'        => "{$userName} membatalkan reservasi {$reservation->trx_id}.",
                        'reservation_id' => $reservation->id,
                        'trx_id'         => $reservation->trx_id,
                    ],
                    'is_read' => 0,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Cancel admin notification failed: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
            ]);
        }

        return redirect()->route('home')->with('successCanceled', true);
    }
}