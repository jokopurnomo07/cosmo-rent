<?php

namespace App\Http\Controllers\User;

use Midtrans\Snap;
use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'vehicle', 'rental_package', 'services'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        // Generate payment URLs dynamically for each reservation
        foreach ($reservations as $reservation) {
            $reservation->payment_url = $this->createPayment($reservation);
        }

        $notifications = Notification::where('is_read', false)->latest()->paginate(10);
        
        return view('user.reservation.index', compact('reservations', 'notifications'));
    }


    public function show($id)
    {
        $reservation = Reservation::with(['user:id,name,email,phone,address', 'services', 'vehicle', 'rental_package'])
            ->findOrFail($id);
        
        return view('user.reservation.show', compact('reservation'));
    }

    public function createPayment(Reservation $reservation)
    {
        try {
            $orderId = $reservation->trx_id ?? uniqid('ORD-');
            $amount = $reservation->total_price;

            $customerDetails = [
                'first_name' => $reservation->user->name,
                'email' => $reservation->user->email,
                'phone' => $reservation->user->phone,
            ];

            $itemDetails = [
                [
                    'id' => $reservation->vehicle_id,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => $reservation->vehicle->name,
                ]
            ];

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
                'callbacks' => [
                    'finish' => route('midtrans.notification'),
                ],
            ];

            // Generate payment URL dynamically
            return Snap::createTransaction($params)->redirect_url;
        } catch (\Exception $e) {
            return null; // If an error occurs, return null instead of breaking the app
        }
    }

}
