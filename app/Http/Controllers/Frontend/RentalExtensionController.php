<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\VehiclePrice;
use App\Models\RentalPackage;
use App\Models\Notification;
use Midtrans\Snap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class RentalExtensionController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // Show Extension Form
    // ─────────────────────────────────────────────────────────────────
    public function create($rentalId)
    {
        $rental = Rental::findOrFail($rentalId);

        // Check ownership
        if ($rental->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if can extend
        if (!RentalExtension::canExtend($rental)) {
            return redirect()->route('user.rentals.index')
                ->with('error', 'Penyewaan ini tidak bisa diperpanjang.');
        }

        // Get vehicle price
        $vehiclePrice = VehiclePrice::where('vehicle_id', $rental->vehicle_id)
            ->select('price_24_hours')
            ->first();

        return view('frontend.rental-extension.create', [
            'rental' => $rental,
            'vehiclePrice' => $vehiclePrice,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Store Extension Request
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request, $rentalId)
    {
        $rental = Rental::findOrFail($rentalId);

        // Check ownership
        if ($rental->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'extension_days' => 'required|integer|min:1|max:7',
        ], [
            'extension_days.required' => 'Jumlah hari perpanjangan harus diisi.',
            'extension_days.integer' => 'Jumlah hari harus berupa angka bulat.',
            'extension_days.min' => 'Perpanjangan minimal 1 hari.',
            'extension_days.max' => 'Perpanjangan maksimal 7 hari.',
        ]);

        try {
            DB::beginTransaction();

            // Check if can extend
            if (!RentalExtension::canExtend($rental)) {
                throw new \Exception('Penyewaan ini tidak bisa diperpanjang.');
            }

            // Calculate new end date
            $newEndDate = $rental->end_date->copy()->addDays($request->extension_days);

            // Get price per 24 hours
            $vehiclePrice = VehiclePrice::where('vehicle_id', $rental->vehicle_id)
                ->select('price_24_hours')
                ->firstOrFail();

            // Calculate additional price
            $additionalPrice = $vehiclePrice->price_24_hours * $request->extension_days;

            // Create extension request
            $extension = RentalExtension::create([
                'rental_id' => $rental->id,
                'extended_until' => $newEndDate,
                'additional_price' => $additionalPrice,
                'status' => 'pending',
            ]);

            DB::commit();

            // Notify admin (DB) and broadcast real-time event
            $this->notifyAdmin($rental, $extension);
            try {
                event(new \App\Events\ExtensionRequested($extension));
            } catch (\Throwable $e) {
                Log::warning('Failed to broadcast ExtensionRequested: ' . $e->getMessage());
            }

            return redirect()->route('user.rentals.index')
                ->with('success', 'Permintaan perpanjangan telah dikirim ke admin.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Extension store failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat permintaan perpanjangan.');
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Finish Page (After Midtrans Payment)
    // ─────────────────────────────────────────────────────────────────
    public function finish(Request $request)
    {
        // This route is used as Midtrans finish callback for extension payments.
        // Prefer webhook processing, but as a fallback handle redirect here
        // to update extension status when user returns from payment page.
        $transactionStatus = $request->query('transaction_status', 'unknown');
        $orderId = $request->query('order_id', null);

        // If this looks like an extension order, try to process it.
        try {
            if ($orderId && strpos($orderId, 'EXT-') === 0) {
                $extensionId = explode('-', $orderId)[1] ?? null;
                $extension = RentalExtension::with('rental.vehicle')->find($extensionId);

                if ($extension) {
                    // If already paid, just show finish view
                    if ($extension->status === 'paid') {
                        return view('frontend.rental-extension.finish', [
                            'transactionStatus' => $transactionStatus,
                            'orderId' => $orderId,
                        ]);
                    }

                    if (in_array($transactionStatus, ['capture', 'settlement'])) {
                        // Update extension -> paid and extend rental
                        DB::beginTransaction();
                        try {
                            $extension->status = 'paid';
                            $extension->save();

                            $rental = $extension->rental;
                            if ($rental) {
                                $rental->end_date = $extension->extended_until;
                                $rental->save();
                            }

                            DB::commit();

                            // Notify user
                            try {
                                if ($rental && $rental->user_id) {
                                    Notification::create([
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
                            } catch (\Throwable $e) {
                                Log::warning('Failed to create extension paid notification (fallback): ' . $e->getMessage());
                            }

                            // Broadcast
                            try {
                                event(new \App\Events\ExtensionPaid($extension));
                            } catch (\Throwable $e) {
                                Log::warning('Failed to broadcast ExtensionPaid (fallback): ' . $e->getMessage());
                            }

                            return redirect()->route('user.rentals.index')
                                ->with('success', 'Pembayaran perpanjangan berhasil dan masa sewa diperpanjang.');
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            Log::error('Fallback extension finish processing failed: ' . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Extension finish fallback encountered error: ' . $e->getMessage());
        }

        return view('frontend.rental-extension.finish', [
            'transactionStatus' => $transactionStatus,
            'orderId' => $orderId,
        ]);
    }
    /**
     * Notify admins about a new extension request
     *
     * @param  \App\Models\Rental  $rental
     * @param  \App\Models\RentalExtension  $extension
     * @return void
     */
    protected function notifyAdmin($rental, $extension)
    {
        try {
            $admins = \App\Models\User::role('admin')->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'rental_extension_request',
                    'data' => [
                        'title' => 'Permintaan Perpanjangan Rental',
                        'message' => "{$rental->user->name} meminta perpanjangan untuk rental {$rental->vehicle->name}",
                        'rental_id' => $rental->id,
                        'extension_id' => $extension->id,
                    ],
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admin about extension: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // INDEX - List user's extensions
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $extensions = RentalExtension::with('rental.vehicle')
            ->whereHas('rental', function ($q) { $q->where('user_id', auth()->id()); })
            ->latest()
            ->paginate(15);

        return view('frontend.rental-extension.index', [
            'extensions' => $extensions,
        ]);
    }

    // User triggers creation of Midtrans Snap payment for an approved extension
    public function pay(Request $request, $id)
    {
        $extension = RentalExtension::with('rental.user', 'rental.vehicle')->findOrFail($id);

        // Ensure owner
        if ($extension->rental->user_id !== auth()->id()) {
            abort(403);
        }

        if ($extension->status !== 'approved') {
            return redirect()->back()->with('error', 'Perpanjangan tidak tersedia untuk pembayaran.');
        }

        try {
            // If payment_url already exists, redirect
            if ($extension->payment_url) {
                return redirect()->away($extension->payment_url);
            }

            // Initialize Midtrans config (use config() or fallback to env)
            \Midtrans\Config::$serverKey    = config('midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = config('midtrans.is_production') ?? env('MIDTRANS_IS_PRODUCTION', false);

            $orderId = 'EXT-' . $extension->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $extension->additional_price,
                ],
                'customer_details' => [
                    'first_name' => $extension->rental->user?->name ?? '-',
                    'email' => $extension->rental->user?->email ?? '-',
                    'phone' => $extension->rental->user?->phone ?? '-',
                ],
                'item_details' => [
                    [
                        'id' => 'EXT-' . $extension->id,
                        'price' => $extension->additional_price,
                        'quantity' => 1,
                        'name' => 'Perpanjangan: ' . ($extension->rental->vehicle->name ?? '-'),
                    ],
                ],
                'callbacks' => [
                    'finish' => route('extension.finish'),
                ],
            ];

            Log::info('Midtrans createTransaction params for extension ' . $extension->id . ': ' . json_encode($params));
            $resp = Snap::createTransaction($params);

            // Debug: log full response to help diagnose missing redirect_url
            Log::info('Midtrans createTransaction response for extension ' . $extension->id . ': ' . json_encode($resp));

            $paymentUrl = $resp->redirect_url ?? $resp->redirect_url ?? null;

            if (! $paymentUrl) {
                Log::error('Midtrans did not return redirect_url for extension ' . $extension->id . '. Full resp: ' . json_encode($resp));
                return redirect()->back()->with('error', 'Gagal membuat link pembayaran. Silakan hubungi admin.');
            }

            // Save on extension (also persist full response for debugging)
            $extension->payment_url = $paymentUrl;
            $extension->midtrans_order_id = $orderId;
            $extension->payment_response = json_encode($resp);
            if (! $extension->payment_due_at) {
                $extension->payment_due_at = now()->addDay();
            }
            $extension->save();

            return redirect()->away($paymentUrl);

        } catch (\Exception $e) {
            Log::error('Failed to create payment for extension (user): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat link pembayaran. Silakan coba lagi.');
        }
    }

    // Debug helper to attempt creating Midtrans transaction and return raw response
    public function debugMidtrans(Request $request, $id)
    {
        if (env('APP_ENV') !== 'local') {
            abort(404);
        }

        $extension = RentalExtension::with('rental.vehicle', 'rental.user')->findOrFail($id);

        try {
            \Midtrans\Config::$serverKey    = config('midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = config('midtrans.is_production') ?? env('MIDTRANS_IS_PRODUCTION', false);

            $orderId = 'DBG-EXT-' . $extension->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $extension->additional_price,
                ],
                'customer_details' => [
                    'first_name' => $extension->rental->user?->name ?? '-',
                    'email' => $extension->rental->user?->email ?? '-',
                    'phone' => $extension->rental->user?->phone ?? '-',
                ],
                'item_details' => [
                    [
                        'id' => 'EXT-' . $extension->id,
                        'price' => $extension->additional_price,
                        'quantity' => 1,
                        'name' => 'Perpanjangan: ' . ($extension->rental->vehicle->name ?? '-'),
                    ],
                ],
                'callbacks' => [
                    'finish' => route('extension.finish'),
                ],
            ];

            $resp = Snap::createTransaction($params);

            return response()->json([
                'ok' => true,
                'resp' => $resp,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
