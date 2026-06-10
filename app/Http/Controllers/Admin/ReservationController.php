<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Midtrans\Snap;
use App\Models\User;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\Notification;
use App\Models\RentalPackage;
use App\Models\VehiclePrice;
use App\Models\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\VehicleAvailabilityNotification;
use App\Mail\ReservationRejectionNotification;

class ReservationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────
    public function index($status)
    {
        if ($status === 'pending') {
            $statusFilter = ['pending'];
        } elseif ($status === 'canceled') {
            $statusFilter = ['canceled', 'rejected'];
        } else {
            $statusFilter = ['confirmed', 'paid'];
        }

        $reservations = Reservation::with(['user', 'vehicle', 'rental_package', 'services'])
            ->whereIn('status', $statusFilter)
            ->latest()
            ->paginate(10);

        // ── Conflict map ─────────────────────────────────────────────
        $pageIds    = $reservations->pluck('id');
        $vehicleIds = $reservations->pluck('vehicle_id')->unique();

        $confirmedOthers = Reservation::whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', ['confirmed', 'paid'])
            ->whereNotIn('id', $pageIds)
            ->get(['id', 'vehicle_id', 'start_date', 'end_date', 'trx_id']);

        $conflictMap = [];
        foreach ($reservations as $res) {
            $conflict = $confirmedOthers->first(function ($other) use ($res) {
                return $other->vehicle_id === $res->vehicle_id
                    && Carbon::parse($other->start_date) < Carbon::parse($res->end_date)
                    && Carbon::parse($other->end_date)   > Carbon::parse($res->start_date);
            });
            $conflictMap[$res->id] = $conflict?->trx_id;
        }

        $notifications = Notification::where('is_read', false)->latest()->paginate(10);

        return view('admin.reservation.index', [
            'reservation'   => $reservations,
            'conflictMap'   => $conflictMap,
            'notifications' => $notifications,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE FORM
    // ─────────────────────────────────────────────────────────────────
    public function create()
    {
        $services       = Service::all();
        $rentalPackages = RentalPackage::all();

        return view('admin.reservation.create', compact('services', 'rentalPackages'));
    }

    // ─────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'type'              => 'required|in:motorcycle,car',
            'service_id'        => 'required_if:type,car|nullable|exists:services,id',
            'rental_package_id' => 'required|exists:rental_packages,id',
            'start_rent'        => 'required|date|after_or_equal:today',
            'time_pickup'       => 'required|date_format:H:i',
            'vehicle_id'        => 'required|exists:vehicles,id',
            'address_pickup'    => 'required|string|min:10|max:255',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
        ], [
            'user_id.required'           => 'Pemesan harus dipilih.',
            'user_id.exists'             => 'User yang dipilih tidak valid.',
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

            $reservation = Reservation::create([
                'user_id'           => $request->user_id,
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

            if ($request->type === 'car' && $request->service_id) {
                ReservationService::create([
                    'reservation_id' => $reservation->id,
                    'service_id'     => $request->service_id,
                ]);
            }

            $reservation->loadMissing('user');
            $userName = $reservation->user?->name ?? 'Seseorang';

            $admins = User::role('admin')->get(['id']);
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type'    => 'new_reservation',
                    'data'    => [
                        'title'          => 'Reservasi Baru',
                        'message'        => "Reservasi baru dari {$userName} (TRX: {$reservation->trx_id}) menunggu konfirmasi.",
                        'reservation_id' => $reservation->id,
                        'trx_id'         => $reservation->trx_id,
                    ],
                    'is_read' => 0,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.reservations.index', 'pending')
                ->with('success', 'Reservasi berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan reservasi: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle', 'rental_package']);

        return view('admin.reservation.show', compact('reservation'));
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT FORM
    // ─────────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $reservation = Reservation::with(['user', 'vehicle', 'services', 'rental_package'])
            ->findOrFail($id);

        $services       = Service::all();
        $rentalPackages = RentalPackage::all();

        return view('admin.reservation.edit', compact('reservation', 'services', 'rentalPackages'));
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'type'              => 'required|in:car,motorcycle',
            'rental_package_id' => 'required|exists:rental_packages,id',
            'vehicle_id'        => 'required|exists:vehicles,id',
            'user_id'           => 'required|exists:users,id',
            'start_rent'        => 'required|date',
            'time_pickup'       => 'required',
            'address_pickup'    => 'required|string',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'service_id'        => 'required_if:type,car|nullable|exists:services,id',
        ], [
            'type.required'              => 'Tipe kendaraan wajib dipilih.',
            'rental_package_id.required' => 'Paket sewa wajib dipilih.',
            'vehicle_id.required'        => 'Kendaraan wajib dipilih.',
            'user_id.required'           => 'Pemesan wajib dipilih.',
            'start_rent.required'        => 'Tanggal mulai wajib diisi.',
            'time_pickup.required'       => 'Waktu pengambilan wajib diisi.',
            'address_pickup.required'    => 'Alamat penjemputan wajib diisi.',
            'service_id.required_if'     => 'Layanan wajib dipilih untuk tipe mobil.',
        ]);

        try {
            DB::beginTransaction();

            $reservation   = Reservation::findOrFail($id);
            $rentalPackage = RentalPackage::findOrFail($request->rental_package_id);

            $startDate = Carbon::parse($request->start_rent . ' ' . $request->time_pickup);
            $endDate   = $startDate->copy()->addHours($rentalPackage->duration_hours);

            $priceColumn  = 'price_' . $rentalPackage->duration_hours . '_hours';
            $vehiclePrice = VehiclePrice::where('vehicle_id', $request->vehicle_id)
                ->select('id', $priceColumn)
                ->firstOrFail();

            $totalPrice = $vehiclePrice->{$priceColumn};

            $reservation->update([
                'user_id'           => $request->user_id,
                'vehicle_id'        => $request->vehicle_id,
                'rental_package_id' => $request->rental_package_id,
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'time_pickup'       => $request->time_pickup,
                'address_pickup'    => $request->address_pickup,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'total_price'       => $totalPrice,
            ]);

            ReservationService::where('reservation_id', $reservation->id)->delete();

            if ($request->type === 'car' && $request->service_id) {
                ReservationService::create([
                    'reservation_id' => $reservation->id,
                    'service_id'     => $request->service_id,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.reservations.index', 'pending')
                ->with('success', 'Reservasi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui reservasi: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // AJAX: Search users
    // ─────────────────────────────────────────────────────────────────
    public function searchUser(Request $request)
    {
        $q = $request->input('q', '');

        $users = User::where('name', 'LIKE', "%{$q}%")
            ->orWhere('email', 'LIKE', "%{$q}%")
            ->select('id', 'name', 'email', 'phone')
            ->limit(10)
            ->get();

        return response()->json([
            'items' => $users->map(fn($u) => [
                'id'    => $u->id,
                'text'  => $u->name . ' (' . $u->email . ')',
                'phone' => $u->phone ?? '',
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // AJAX: Search vehicles
    // ─────────────────────────────────────────────────────────────────
    public function searchVehicle(Request $request)
    {
        $q    = $request->input('q', '');
        $type = $request->input('vehicle_type');

        $query = Vehicle::where('status', '!=', 'rented');

        if ($type) {
            $query->where('type', $type);
        }
        if ($q) {
            $query->where('name', 'LIKE', "%{$q}%");
        }

        $vehicles = $query->select('id', 'name')->limit(10)->get();

        return response()->json([
            'items' => $vehicles->map(fn($v) => [
                'id'   => $v->id,
                'text' => $v->name,
            ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE MIDTRANS PAYMENT LINK
    // ─────────────────────────────────────────────────────────────────
    public function createPayment($reservation)
    {
        try {
            // Ensure Midtrans config is initialized
            \Midtrans\Config::$serverKey    = config('midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = config('midtrans.is_production') ?? env('MIDTRANS_IS_PRODUCTION', false);

            $amount = $reservation->total_price;

            $params = [
                'transaction_details' => [
                    'order_id'     => $reservation->trx_id,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $reservation->user?->name  ?? '-',
                    'email'      => $reservation->user?->email ?? '-',
                    'phone'      => $reservation->user?->phone ?? '-',
                ],
                'item_details' => [
                    [
                        'id'       => $reservation->vehicle_id,
                        'price'    => $amount,
                        'quantity' => 1,
                        'name'     => $reservation->vehicle->name,
                    ],
                ],
                'callbacks' => [
                    'finish' => route('payment.finish'),
                ],
            ];

            Log::info('Midtrans createPayment params', ['reservation_id' => $reservation->id, 'params' => $params]);

            $resp = Snap::createTransaction($params);

            Log::info('Midtrans createPayment response', ['reservation_id' => $reservation->id, 'resp' => $resp]);

            return $resp->redirect_url ?? null;

        } catch (\Exception $e) {
            Log::error('Midtrans createPayment error: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'trx_id'         => $reservation->trx_id,
            ]);
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE STATUS
    // ─────────────────────────────────────────────────────────────────
    public function updateStatus(Request $request)
    {
        $reservation = Reservation::findOrFail($request->id);
        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

        // ── Guard: double-confirm ─────────────────────────────────────
        // Mencegah admin klik confirm dua kali — Midtrans menolak order_id
        // yang sama sehingga createPayment() akan return null dan response
        // akan 500, tapi status sudah terlanjur ter-set. Guard ini
        // memutus loop sebelum sampai ke Midtrans.
        if ($request->status === 'confirmed' && $reservation->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi ini sudah dikonfirmasi sebelumnya.',
            ], 422);
        }

        // ── Guard: jangan ubah status reservasi yang sudah paid ───────
        // Reservasi yang sudah paid berarti sudah ada rental aktif dan
        // kendaraan sudah ter-lock. Perubahan status di sini bisa
        // menyebabkan inkonsistensi dengan tabel rentals.
        if ($reservation->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi yang sudah dibayar tidak dapat diubah statusnya di sini. Gunakan manajemen rental.',
            ], 422);
        }

        $autoCanceled = 0;

        // ── Rejected / Canceled ──────────────────────────────────────
        if (in_array($request->status, ['rejected', 'canceled'])) {
            $reservation->status          = $request->status;
            $reservation->reason_canceled = $request->reason;

            $isRejected = $request->status === 'rejected';
            $emailLabel = $isRejected ? 'Penolakan' : 'Pembatalan';
            $notifTitle = $isRejected ? 'Reservasi Ditolak' : 'Reservasi Dibatalkan';
            $notifMsg   = $isRejected
                ? "Reservasi {$reservation->trx_id} Anda ditolak. Alasan: {$request->reason}"
                : "Reservasi {$reservation->trx_id} Anda dibatalkan. Alasan: {$request->reason}";

            if ($reservation->user) {
                Mail::to($reservation->user->email)
                    ->send(new ReservationRejectionNotification($reservation, $emailLabel));
            }

            if ($reservation->user_id) {
                Notification::create([
                    'user_id' => $reservation->user_id,
                    'type'    => $request->status === 'rejected' ? 'reservation_rejected' : 'reservation_canceled',
                    'data'    => [
                        'title'          => $notifTitle,
                        'message'        => $notifMsg,
                        'reservation_id' => $reservation->id,
                        'trx_id'         => $reservation->trx_id,
                    ],
                    'is_read' => 0,
                ]);
            }
        }

        // ── Confirmed ────────────────────────────────────────────────
        if ($request->status === 'confirmed') {

            $conflicts = Reservation::where('vehicle_id', $reservation->vehicle_id)
                ->whereIn('status', ['confirmed', 'paid'])
                ->where('id', '!=', $reservation->id)
                ->where('start_date', '<', $reservation->end_date)
                ->where('end_date',   '>', $reservation->start_date)
                ->with('user')
                ->get();

            foreach ($conflicts as $conflict) {
                $cancelReason = 'Otomatis dibatalkan karena kendaraan yang sama '
                    . 'telah dikonfirmasi untuk reservasi ' . $reservation->trx_id
                    . ' pada periode yang tumpang tindih.';

                $conflict->status          = 'canceled';
                $conflict->reason_canceled = $cancelReason;
                $conflict->save();

                if ($conflict->user) {
                    Mail::to($conflict->user->email)
                        ->send(new ReservationRejectionNotification($conflict, 'Pembatalan'));
                }

                if ($conflict->user_id) {
                    Notification::create([
                        'user_id' => $conflict->user_id,
                        'type'    => 'reservation_canceled',
                        'data'    => [
                            'title'          => 'Reservasi Dibatalkan',
                            'message'        => "Reservasi {$conflict->trx_id} Anda otomatis dibatalkan karena kendaraan sudah dikonfirmasi untuk reservasi lain.",
                            'reservation_id' => $conflict->id,
                            'trx_id'         => $conflict->trx_id,
                        ],
                        'is_read' => 0,
                    ]);
                }

                $autoCanceled++;
            }

            $paymentUrl = $this->createPayment($reservation);

            if (! $paymentUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat link pembayaran. Cek log Laravel untuk detail error.',
                ], 500);
            }

            $reservation->status      = 'confirmed';
            $reservation->payment_url = $paymentUrl;

            if ($reservation->user) {
                Mail::to($reservation->user->email)
                    ->send(new VehicleAvailabilityNotification($reservation, $paymentUrl));
            }

            if ($reservation->user_id) {
                Notification::create([
                    'user_id' => $reservation->user_id,
                    'type'    => 'reservation_confirmed',
                    'data'    => [
                        'title'          => 'Reservasi Dikonfirmasi',
                        'message'        => "Reservasi {$reservation->trx_id} Anda telah dikonfirmasi. Silakan lakukan pembayaran melalui link yang dikirim ke email Anda.",
                        'reservation_id' => $reservation->id,
                        'trx_id'         => $reservation->trx_id,
                    ],
                    'is_read' => 0,
                ]);
            }
        }

        $reservation->save();

        $message = $autoCanceled > 0
            ? "Status berhasil diperbarui. {$autoCanceled} reservasi konflik otomatis dibatalkan."
            : 'Status berhasil diperbarui.';

        return response()->json([
            'success'       => true,
            'auto_canceled' => $autoCanceled,
            'message'       => $message,
        ]);
    }
}