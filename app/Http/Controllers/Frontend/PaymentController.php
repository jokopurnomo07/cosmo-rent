<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Rental;
use App\Models\Reservation;
use App\Models\RentalService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

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
        if ($reservation->status !== 'failed' && $reservation->status !== 'expired') {
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

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid notification'], 400);
        }
    }

    protected function handleSuccess(Reservation $reservation): void
    {
        if ($reservation->status === 'paid') {
            return;
        }

        $reservation->update(['status' => 'paid']);

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

        if ($reservation->vehicle->type === 'car' && $reservation->services->isNotEmpty()) {
            foreach ($reservation->services as $service) {
                RentalService::updateOrCreate([
                    'rental_id'  => $rental->id,
                    'service_id' => $service->id,
                ]);
            }
        }

        Log::info('Payment success, rental created/updated: ' . $reservation->trx_id);
    }

    protected function handleFailure(Reservation $reservation): void
    {
        if (in_array($reservation->status, ['failed', 'expired'])) {
            return;
        }

        $reservation->update(['status' => 'expired']);

        Rental::where('trx_id', $reservation->trx_id)
            ->update(['status' => 'payment_failed']);

        Log::info('Payment failed: ' . $reservation->trx_id);
    }

    protected function handlePending(Reservation $reservation): void
    {
        $reservation->update(['status' => 'pending']);
        Log::info('Payment pending: ' . $reservation->trx_id);
    }
}