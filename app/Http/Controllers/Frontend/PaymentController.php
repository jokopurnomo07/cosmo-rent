<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Rental;
use Midtrans\Notification;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Models\RentalService;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function notificationHandler(Request $request)
    {
        // Initialize Midtrans Notification
        $notification = new Notification();

        // Get the order ID and transaction status from the notification
        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        // Find the corresponding reservation
        $reservation = Reservation::where('trx_id', $orderId)->first();
        $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        // Handle different transaction statuses
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                // Payment success
                $this->handleSuccess($reservation);
            } elseif ($fraudStatus == 'challenge') {
                // Payment under review
                $this->handlePending($reservation);
            }
        } elseif ($transactionStatus == 'settlement') {
            // Payment settled
            $this->handleSuccess($reservation);
        } elseif ($transactionStatus == 'pending') {
            // Payment is pending
            $this->handlePending($reservation);
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            // Payment failed
            $this->handleFailure($reservation);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleSuccess($reservation)
    {
        // Update or create the rental record
        $rental = Rental::updateOrCreate(
            ['trx_id' => $reservation->trx_id], // Match the rental by reservation ID
            [
                'user_id' => $reservation->vehicle_id,
                'vehicle_id' => $reservation->vehicle_id,
                'rental_package_id' => $reservation->rental_package_id,
                'start_date' => $reservation->start_rent,
                'end_date' => $reservation->end_rent,
                'time_pickup' => $reservation->time_pickup,
                'nama_guest' => $reservation->nama_guest,
                'email_guest' => $reservation->email_guest,
                'no_hp_guest' => $reservation->no_hp_guest,
                'address_pickup' => $reservation->address_pickup,
                'latitude' => $reservation->latitude,
                'longitude' => $reservation->longitude,
                'total_price' => $reservation->total_price,
                'status' => 'paid',
                'trx_id' => $reservation->trx_id,
            ]
        );

        // If it's a car rental, add related services
        if ($reservation->type == 'car') {
            RentalService::updateOrCreate(
                ['rental_id' => $rental->id, 'service_id' => $reservation->services->id],
                []
            );
        }

        // Additional logic such as sending a confirmation email
    }

    protected function handleFailure($reservation)
    {
        // Update the rental status to canceled
        $rental = Rental::where('trx_id', $reservation->trx_id)->first();

        if ($rental) {
            $rental->update(['status' => 'payment_failed']);
        }

        // Additional logic for failure case
    }

    protected function handlePending($reservation)
    {
        // Update the rental status to pending
        $rental = Rental::where('trx_id', $reservation->trx_id)->first();

        if ($rental) {
            $rental->update(['status' => 'pending']);
        }
    }
}
