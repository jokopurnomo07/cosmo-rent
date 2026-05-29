<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Payment;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

/**
 * Midtrans is an external service. These tests mock HTTP calls
 * and the Midtrans SDK so we never hit real endpoints.
 *
 * NOTE: Adjust model names if your Payment/Reservation models differ.
 */
class PaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Set fake Midtrans key so AppServiceProvider doesn't throw
        config(['midtrans.server_key' => 'SB-Mid-server-fakekey']);

        Role::create(['name' => 'user',  'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->user->assignRole('user');
    }

    // -------------------------------------------------------------------------
    // PAYMENT PAGE ACCESS
    // -------------------------------------------------------------------------

    /** @test */
    public function payment_page_loads_for_valid_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.create', ['reservation_id' => $reservation->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function payment_page_is_blocked_for_guest(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);

        $response = $this->get(route('payments.create', ['reservation_id' => $reservation->id]));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_cannot_access_payment_page_for_another_users_reservation(): void
    {
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $otherUser->assignRole('user');

        $reservation = Reservation::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.create', ['reservation_id' => $reservation->id]));

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_page_blocks_already_paid_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.create', ['reservation_id' => $reservation->id]));

        // Should redirect with error — paying twice not allowed
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    // -------------------------------------------------------------------------
    // PAYMENT STORE (Midtrans Snap token creation)
    // -------------------------------------------------------------------------

    /** @test */
    public function payment_store_creates_transaction_and_returns_snap_token(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'confirmed',
        ]);

        // Mock Midtrans Snap so we don't call real API
        \Midtrans\Snap::shouldReceive('getSnapToken')
            ->once()
            ->andReturn('fake-snap-token-abc123');

        $response = $this->actingAs($this->user)
            ->post(route('payments.store'), [
                'reservation_id' => $reservation->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['snap_token' => 'fake-snap-token-abc123']);
    }

    // -------------------------------------------------------------------------
    // MIDTRANS NOTIFICATION HANDLER
    // -------------------------------------------------------------------------

    /** @test */
    public function midtrans_notification_updates_payment_status_on_success(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);
        $payment     = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'status'         => 'pending',
            'order_id'       => 'ORDER-' . $reservation->id,
        ]);

        // Simulate Midtrans posting a settlement notification
        $payload = [
            'order_id'           => 'ORDER-' . $reservation->id,
            'status_code'        => '200',
            'gross_amount'       => '500000.00',
            'signature_key'      => $this->generateMidtransSignature(
                'ORDER-' . $reservation->id,
                '200',
                '500000.00'
            ),
            'transaction_status' => 'settlement',
            'fraud_status'       => 'accept',
            'payment_type'       => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'settlement',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function midtrans_notification_handles_pending_status(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);
        $payment     = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'status'         => 'pending',
            'order_id'       => 'ORDER-' . $reservation->id,
        ]);

        $payload = [
            'order_id'           => 'ORDER-' . $reservation->id,
            'status_code'        => '201',
            'gross_amount'       => '500000.00',
            'signature_key'      => $this->generateMidtransSignature(
                'ORDER-' . $reservation->id,
                '201',
                '500000.00'
            ),
            'transaction_status' => 'pending',
            'payment_type'       => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'pending', // unchanged
        ]);
    }

    /** @test */
    public function midtrans_notification_handles_cancel_status(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);
        $payment     = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'status'         => 'pending',
            'order_id'       => 'ORDER-' . $reservation->id,
        ]);

        $payload = [
            'order_id'           => 'ORDER-' . $reservation->id,
            'status_code'        => '200',
            'gross_amount'       => '500000.00',
            'signature_key'      => $this->generateMidtransSignature(
                'ORDER-' . $reservation->id,
                '200',
                '500000.00'
            ),
            'transaction_status' => 'cancel',
            'payment_type'       => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'cancel',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function midtrans_notification_rejects_invalid_signature(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'confirmed']);

        $payload = [
            'order_id'           => 'ORDER-' . $reservation->id,
            'status_code'        => '200',
            'gross_amount'       => '500000.00',
            'signature_key'      => 'invalid-signature',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        // Should reject tampered notifications
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PAYMENT FINISH
    // -------------------------------------------------------------------------

    /** @test */
    public function payment_finish_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('payment.finish'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    /**
     * Generate a valid Midtrans signature key for sandbox.
     * Formula: SHA512(order_id + status_code + gross_amount + server_key)
     */
    private function generateMidtransSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount
    ): string {
        $serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-fakekey');

        return hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    }
}