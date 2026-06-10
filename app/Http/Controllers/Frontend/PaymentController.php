<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Rental;
use App\Models\Reservation;
use App\Models\RentalService;
use App\Models\RentalExtension;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create($reservation_id)
    {
        $reservation = Reservation::where('id', $reservation_id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['confirmed'])
            ->firstOrFail();

        if (!$reservation->payment_url) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Link pembayaran belum tersedia. Hubungi admin.');
        }

        return redirect()->away($reservation->payment_url);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function paymentFinish(Request $request)
    {
        $transactionStatus = $request->query('transaction_status');
        $orderId           = $request->query('order_id');

        if (!$orderId) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Data pembayaran tidak valid.');
        }

        // Fallback: if this is an extension order and Midtrans didn't send webhook,
        // process the success here based on redirect params (best-effort).
        try {
            if (strpos($orderId, 'EXT-') === 0) {
                $extensionId = explode('-', $orderId)[1] ?? null;
                $extension = \App\Models\RentalExtension::with('rental')->find($extensionId);

                if ($extension) {
                    if (in_array($transactionStatus, ['capture', 'settlement'])) {
                        $this->handleExtensionSuccess($extension);

                        return redirect()->route('user.rentals.index')
                            ->with('success', 'Pembayaran perpanjangan berhasil dan masa sewa diperpanjang.');
                    }

                    if ($transactionStatus === 'pending') {
                        return redirect()->route('user.rentals.index')
                            ->with('info', 'Pembayaran perpanjangan sedang diproses.');
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Fallback extension finish processing failed: ' . $e->getMessage());
        }

        $reservation = Reservation::where('trx_id', $orderId)
            ->where('user_id', auth()->id())
            ->with(['vehicle', 'services'])
            ->first();

        if (!$reservation) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'Reservasi tidak ditemukan.');
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            if ($reservation->status !== 'paid') {
                $this->handleSuccess($reservation);
            }

            return redirect()->route('user.reservations.index')
                ->with('success', 'Pembayaran berhasil! Reservasi kamu sudah aktif.');
        }

        if ($transactionStatus === 'pending') {
            return redirect()->route('user.reservations.index')
                ->with('info', 'Pembayaran sedang diproses. Kami akan konfirmasi segera.');
        }

        // deny, expire, cancel
        if (!in_array($reservation->status, ['failed', 'expired'])) {
            $this->handleFailure($reservation);
        }

        return redirect()->route('user.reservations.index')
            ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
    }

    public function notificationHandler(Request $request)
    {
        try {
            \Midtrans\Config::$serverKey    = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            $notification      = new \Midtrans\Notification();
            $orderId           = $notification->order_id;
            $transactionStatus = $notification->transaction_status;

            // Check if this is an extension payment (order_id starts with 'EXT-')
            if (strpos($orderId, 'EXT-') === 0) {
                $extensionId = explode('-', $orderId)[1];
                $extension = RentalExtension::find($extensionId);

                if (!$extension) {
                    return response()->json(['error' => 'Extension not found'], 404);
                }

                match ($transactionStatus) {
                    'capture', 'settlement'    => $this->handleExtensionSuccess($extension),
                    'pending'                  => $this->handleExtensionPending($extension),
                    'deny', 'expire', 'cancel' => $this->handleExtensionFailure($extension),
                    default                    => Log::info('Unhandled extension transaction status: ' . $transactionStatus),
                };
            } else {
                // Regular reservation payment
                $reservation = Reservation::where('trx_id', $orderId)
                    ->with(['user:id,name,email,phone,address', 'services', 'vehicle'])
                    ->first();

                if (!$reservation) {
                    return response()->json(['error' => 'Reservation not found'], 404);
                }

                match ($transactionStatus) {
                    'capture', 'settlement'    => $this->handleSuccess($reservation),
                    'pending'                  => $this->handlePending($reservation),
                    'deny', 'expire', 'cancel' => $this->handleFailure($reservation),
                    default                    => Log::info('Unhandled transaction status: ' . $transactionStatus),
                };
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid notification'], 400);
        }
    }

    protected function handleSuccess(Reservation $reservation): void
    {
        // Guard idempoten — Midtrans bisa kirim notifikasi duplikat
        if ($reservation->status === 'paid') {
            return;
        }

        $reservation->update(['status' => 'paid']);

        // Lock kendaraan segera setelah pembayaran settlement.
        // Ini mencegah kendaraan yang sudah dibayar muncul sebagai
        // available di form pemesanan baru sebelum admin set 'ongoing'.
        // Admin set 'ongoing' tetap ada tapi fungsinya konfirmasi fisik
        // (kendaraan diserahkan), bukan untuk block availability.
        if ($reservation->vehicle && $reservation->vehicle->status === 'available') {
            $reservation->vehicle->update(['status' => 'rented']);
        }

        $rental = Rental::updateOrCreate(
            ['trx_id' => $reservation->trx_id],
            [
                'user_id'           => $reservation->user_id,
                'vehicle_id'        => $reservation->vehicle_id,
                'rental_package_id' => $reservation->rental_package_id,
                'start_date'        => $reservation->start_date,
                'end_date'          => $reservation->end_date,
                'time_pickup'       => $reservation->time_pickup,
                'address_pickup'    => $reservation->address_pickup,
                'latitude'          => $reservation->latitude,
                'longitude'         => $reservation->longitude,
                'total_price'       => $reservation->total_price,
                'status'            => 'paid',
                'trx_id'            => $reservation->trx_id,
            ]
        );

        if ($reservation->vehicle && $reservation->vehicle->type === 'car' && $reservation->services->isNotEmpty()) {
            foreach ($reservation->services as $service) {
                RentalService::updateOrCreate([
                    'rental_id'  => $rental->id,
                    'service_id' => $service->id,
                ]);
            }
        }

        Log::info('Payment success, rental created/updated, vehicle locked: ' . $reservation->trx_id);
    }

    protected function handleFailure(Reservation $reservation): void
    {
        // Guard idempoten
        if (in_array($reservation->status, ['failed', 'expired'])) {
            return;
        }

        $reservation->update(['status' => 'expired']);

        // Bebaskan kendaraan jika sebelumnya sudah ter-lock
        // (kasus: payment sempat pending lalu expire)
        if ($reservation->vehicle && $reservation->vehicle->status === 'rented') {
            // Cek dulu apakah ada rental/reservation lain yang masih aktif
            // untuk kendaraan yang sama sebelum dibebaskan
            $otherActivePaid = Reservation::where('vehicle_id', $reservation->vehicle_id)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['paid', 'confirmed'])
                ->exists();

            if (!$otherActivePaid) {
                $reservation->vehicle->update(['status' => 'available']);
            }
        }

        Rental::where('trx_id', $reservation->trx_id)
            ->update(['status' => 'payment_failed']);

        Log::info('Payment failed, reservation expired: ' . $reservation->trx_id);
    }

    protected function handlePending(Reservation $reservation): void
    {
        $reservation->update(['status' => 'pending']);
        Log::info('Payment pending: ' . $reservation->trx_id);
    }

    // ─────────────────────────────────────────────────────────────────
    // EXTENSION PAYMENT HANDLERS
    // ─────────────────────────────────────────────────────────────────
    protected function handleExtensionSuccess(RentalExtension $extension): void
    {
        // Guard idempoten
        if ($extension->status === 'paid') {
            return;
        }

        try {
            DB::beginTransaction();

            $rental = $extension->rental;

            // Update extension status
            $extension->status = 'paid';
            $extension->save();

            // Update rental end_date
            $rental->end_date = $extension->extended_until;
            $rental->save();

            DB::commit();

            // Notify user about successful extension payment
            try {
                if ($rental->user_id) {
                    \App\Models\Notification::create([
                        'user_id' => $rental->user_id,
                        'type' => 'extension_paid',
                        'data' => [
                            'title' => 'Perpanjangan Dibayar',
                            'message' => "Perpanjangan untuk {$rental->vehicle->name} telah berhasil dibayar.",
                            'extension_id' => $extension->id,
                            'rental_id' => $rental->id,
                        ],
                        'is_read' => false,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create extension paid notification: ' . $e->getMessage());
            }

            Log::info('Extension payment success: ' . $extension->id . ', new end_date: ' . $extension->extended_until);

            // Broadcast extension paid so header updates in realtime
            try {
                event(new \App\Events\ExtensionPaid($extension));
            } catch (\Throwable $e) {
                Log::warning('Failed to broadcast ExtensionPaid: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Extension payment success handler failed: ' . $e->getMessage());
        }
    }

    protected function handleExtensionFailure(RentalExtension $extension): void
    {
        // Guard idempoten
        if (in_array($extension->status, ['rejected', 'canceled'])) {
            return;
        }

        $extension->status = 'canceled';
        $extension->reason_rejected = 'Pembayaran dibatalkan atau expired';
        $extension->save();

        // Notify user
        try {
            if ($extension->rental && $extension->rental->user_id) {
                \App\Models\Notification::create([
                    'user_id' => $extension->rental->user_id,
                    'type' => 'extension_failed',
                    'data' => [
                        'title' => 'Perpanjangan Gagal',
                        'message' => "Pembayaran perpanjangan untuk {$extension->rental->vehicle->name} gagal atau dibatalkan.",
                        'extension_id' => $extension->id,
                        'rental_id' => $extension->rental->id,
                    ],
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create extension failed notification: ' . $e->getMessage());
        }

        Log::info('Extension payment failed: ' . $extension->id);
    }

    protected function handleExtensionPending(RentalExtension $extension): void
    {
        // Keep status as 'approved' (waiting for payment settlement)
        Log::info('Extension payment pending: ' . $extension->id);
    }
}