<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Rental;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\RentalService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function notificationHandler(Request $request)
    {
        try {
            $notification = new \Midtrans\Notification();

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
            };

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid notification'], 400);
        }
    }

    public function paymentFinish(Request $request)
    {
        return redirect()->route('home')->with('success', 'Pembayaran sedang diproses.');
    }

    protected function handleSuccess($reservation)
    {
        Reservation::where('trx_id', $reservation->trx_id)->update(['status' => 'paid']);

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
    }

    protected function handleFailure($reservation)
    {
        Reservation::where('trx_id', $reservation->trx_id)
            ->update(['status' => 'expired']);

        // If a Rental somehow exists (e.g. a retry scenario), clean it up too
        Rental::where('trx_id', $reservation->trx_id)
            ->update(['status' => 'payment_failed']);
    }

    protected function handlePending($reservation)
    {
        Reservation::where('trx_id', $reservation->trx_id)
            ->update(['status' => 'pending']);
    }
}