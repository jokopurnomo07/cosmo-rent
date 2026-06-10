<?php

namespace App\Http\Controllers\Admin;

use Midtrans\Snap;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Mail\ExtensionApprovedNotification;
use App\Http\Controllers\Frontend\PaymentController as FrontendPaymentController;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\ActivityLogger;
use Carbon\Carbon;

class RentalExtensionController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX - Show Pending Extensions
    // ─────────────────────────────────────────────────────────────────
    public function index($status = 'pending')
    {
        $statusFilter = [];
        
        if ($status === 'pending') {
            $statusFilter = ['pending'];
        } elseif ($status === 'approved') {
            $statusFilter = ['approved'];
        } elseif ($status === 'paid') {
            $statusFilter = ['paid'];
        } else {
            $statusFilter = ['rejected', 'canceled'];
        }

        $extensions = RentalExtension::with([
            'rental' => function ($q) {
                $q->with(['user', 'vehicle']);
            },
            'admin'
        ])
            ->whereIn('status', $statusFilter)
            ->latest()
            ->paginate(15);

        return view('admin.rental-extension.index', [
            'extensions' => $extensions,
            'status' => $status,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW - Extension Details
    // ─────────────────────────────────────────────────────────────────
    public function show($id)
    {
        $extension = RentalExtension::with([
            'rental' => function ($q) {
                $q->with(['user', 'vehicle', 'services']);
            },
            'admin'
        ])->findOrFail($id);

        return view('admin.rental-extension.show', [
            'extension' => $extension,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // APPROVE - Admin Approves Extension & Creates Payment
    // ─────────────────────────────────────────────────────────────────
    public function approve(Request $request, $id)
    {
        $extension = RentalExtension::findOrFail($id);

        if ($extension->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Perpanjangan ini sudah diproses sebelumnya.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $rental = $extension->rental;

            // Admin approves extension. Do NOT create payment here — user pays.
            $extension->status = 'approved';
            $extension->admin_id = auth()->id();
            $extension->admin_notes = $request->notes ?? null;
            $extension->payment_url = null;
            $extension->midtrans_order_id = null;
            $extension->payment_due_at = now()->addDay(); // 24 hours to complete payment by user
            $extension->save();

            DB::commit();

            // Create notification (store title/message in data JSON)
            if ($rental->user_id) {
                Notification::create([
                    'user_id' => $rental->user_id,
                    'type' => 'extension_approved',
                    'data' => [
                        'title' => 'Perpanjangan Rental Disetujui',
                        'message' => "Perpanjangan untuk {$rental->vehicle->name} telah disetujui. Silakan lakukan pembayaran dari laman perpanjangan.",
                        'rental_id' => $rental->id,
                        'extension_id' => $extension->id,
                        'user_id'        => $rental->user_id,
                        'created_at'     => $rental->created_at->toDateTimeString(),
                        'trx_id'         => $rental->trx_id,
                    ],
                    'is_read' => false,
                ]);
            }

            // Broadcast approval so user receives realtime notification if online
            try {
                event(new \App\Events\ExtensionApproved($extension));
            } catch (\Throwable $e) {
                Log::warning('Failed to broadcast ExtensionApproved: ' . $e->getMessage());
            }

            // Activity log
            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($extension)
                    ->withProperties(['rental_id' => $rental->id, 'action' => 'approved'])
                    ->log('Extension approved');
            } catch (\Exception $e) {
                Log::warning('Activity log failed for extension approve: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Perpanjangan telah disetujui. Email pembayaran telah dikirim ke pengguna.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Extension approve failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui perpanjangan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // REJECT - Admin Rejects Extension
    // ─────────────────────────────────────────────────────────────────
    public function reject(Request $request, $id)
    {
        $extension = RentalExtension::findOrFail($id);

        if ($extension->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Perpanjangan ini sudah diproses sebelumnya.',
            ], 422);
        }

        $request->validate([
            'reason' => 'required|string|min:5',
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
            'reason.min' => 'Alasan harus minimal 5 karakter.',
        ]);

        try {
            DB::beginTransaction();

            $rental = $extension->rental;

            $extension->status = 'rejected';
            $extension->admin_id = auth()->id();
            $extension->reason_rejected = $request->reason;
            $extension->save();

            DB::commit();

            // Create notification
            if ($rental->user_id) {
                Notification::create([
                    'user_id' => $rental->user_id,
                    'type' => 'extension_rejected',
                    'data' => [
                        'message' => "Perpanjangan untuk {$rental->vehicle->name} ditolak. Alasan: {$request->reason}",
                        'title' => 'Perpanjangan Rental Ditolak',
                        'rental_id' => $rental->id,
                        'extension_id' => $extension->id,
                    ],
                    'is_read' => false,
                ])->toArray();
            }

            // Activity log
            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($extension)
                    ->withProperties(['rental_id' => $rental->id, 'action' => 'rejected', 'reason' => $request->reason])
                    ->log('Extension rejected');
            } catch (\Exception $e) {
                Log::warning('Activity log failed for extension reject: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Perpanjangan telah ditolak.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Extension reject failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak perpanjangan.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE PAYMENT FOR EXTENSION
    // ─────────────────────────────────────────────────────────────────
    private function createPaymentForExtension(RentalExtension $extension)
    {
        try {
            $rental = $extension->rental;
            $amount = $extension->additional_price;
            $orderId = 'EXT-' . $extension->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $rental->user?->name ?? '-',
                    'email' => $rental->user?->email ?? '-',
                    'phone' => $rental->user?->phone ?? '-',
                ],
                'item_details' => [
                    [
                        'id' => 'EXT-' . $extension->id,
                        'price' => $amount,
                        'quantity' => 1,
                        'name' => 'Perpanjangan: ' . $rental->vehicle->name,
                    ],
                ],
                'callbacks' => [
                    'finish' => route('extension.finish'),
                ],
            ];

            $resp = Snap::createTransaction($params);
            return [
                'url' => $resp->redirect_url ?? ($resp->redirect_url ?? null),
                'order_id' => $orderId,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create Midtrans payment for extension: ' . $e->getMessage());
            return null;
        }
    }

    // Note: simulatePayment removed. Admin should only approve/reject.
}
