<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\RentalPackage;
use App\Models\Reservation;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * NOTE: These tests assume the following factories exist:
 * - Vehicle::factory()
 * - Service::factory()
 * - RentalPackage::factory()
 * - Reservation::factory()
 *
 * Adjust model/factory names if yours differ.
 */
class ReservationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'user',  'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'phone'             => '08123456789',
        ]);
        $this->user->assignRole('user');

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole('admin');
    }

    // -------------------------------------------------------------------------
    // RESERVATION PAGE ACCESS
    // -------------------------------------------------------------------------

    /** @test */
    public function reservation_create_page_is_accessible(): void
    {
        $response = $this->get(route('reservations.create'));

        // Page loads — guest sees login prompt via @auth in blade
        $response->assertStatus(200);
    }

    /** @test */
    public function reservation_page_shows_form_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reservations.create'));

        $response->assertStatus(200);
        // Form should be present
        $response->assertSee('reservations.store');
    }

    /** @test */
    public function reservation_page_pre_fills_vehicle_when_id_given(): void
    {
        $vehicle = Vehicle::factory()->create(['type' => 'car']);

        $response = $this->actingAs($this->user)
            ->get(route('reservations.create', ['id' => $vehicle->id]));

        $response->assertStatus(200);
        $response->assertSee($vehicle->name);
    }

    // -------------------------------------------------------------------------
    // RESERVATION STORE
    // -------------------------------------------------------------------------

    /** @test */
    public function authenticated_user_can_create_a_reservation(): void
    {
        $vehicle       = Vehicle::factory()->create(['type' => 'car', 'is_available' => true]);
        $service       = Service::factory()->create();
        $rentalPackage = RentalPackage::factory()->create();

        $payload = [
            'type'              => 'car',
            'service_id'        => $service->id,
            'masa_sewa'         => 3,
            'rental_package_id' => $rentalPackage->id,
            'start_rent'        => now()->addDay()->format('Y-m-d'),
            'end_rent'          => now()->addDays(4)->format('Y-m-d'),
            'time_pickup'       => '09:00',
            'vehicle_id'        => $vehicle->id,
            'address_pickup'    => 'Jl. Test No. 1',
            'latitude'          => '-6.200000',
            'longitude'         => '106.816666',
            'email_guest'       => $this->user->email,
            'nama_guest'        => $this->user->name,
            'no_hp_guest'       => '08123456789',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('reservations.store'), $payload);

        $this->assertDatabaseHas('reservations', [
            'vehicle_id' => $vehicle->id,
            'email_guest' => $this->user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function reservation_requires_vehicle_id(): void
    {
        $payload = [
            'type'              => 'car',
            'masa_sewa'         => 3,
            'start_rent'        => now()->addDay()->format('Y-m-d'),
            'end_rent'          => now()->addDays(4)->format('Y-m-d'),
            'time_pickup'       => '09:00',
            'vehicle_id'        => '', // missing
            'address_pickup'    => 'Jl. Test No. 1',
            'email_guest'       => $this->user->email,
            'nama_guest'        => $this->user->name,
            'no_hp_guest'       => '08123456789',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('reservations.store'), $payload);

        $response->assertSessionHasErrors('vehicle_id');
    }

    /** @test */
    public function reservation_requires_start_date(): void
    {
        $vehicle = Vehicle::factory()->create();

        $payload = [
            'type'           => 'car',
            'masa_sewa'      => 3,
            'start_rent'     => '', // missing
            'end_rent'       => now()->addDays(4)->format('Y-m-d'),
            'time_pickup'    => '09:00',
            'vehicle_id'     => $vehicle->id,
            'address_pickup' => 'Jl. Test No. 1',
            'email_guest'    => $this->user->email,
            'nama_guest'     => $this->user->name,
            'no_hp_guest'    => '08123456789',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('reservations.store'), $payload);

        $response->assertSessionHasErrors('start_rent');
    }

    /** @test */
    public function guest_cannot_post_a_reservation(): void
    {
        $vehicle = Vehicle::factory()->create();

        $payload = [
            'type'           => 'car',
            'vehicle_id'     => $vehicle->id,
            'masa_sewa'      => 3,
            'start_rent'     => now()->addDay()->format('Y-m-d'),
            'end_rent'       => now()->addDays(4)->format('Y-m-d'),
            'time_pickup'    => '09:00',
            'address_pickup' => 'Jl. Test No. 1',
            'email_guest'    => 'guest@example.com',
            'nama_guest'     => 'Guest User',
            'no_hp_guest'    => '08123456789',
        ];

        $response = $this->post(route('reservations.store'), $payload);

        // Should redirect to login — controller must guard this
        // WARNING: If your controller has no auth check, this test will FAIL
        // and reveals the security gap mentioned in the code review
        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // RESERVATION STATUS UPDATE
    // -------------------------------------------------------------------------

    /** @test */
    public function user_can_cancel_their_own_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reservations.update-status', [
                'status' => 'cancelled',
                'id'     => $reservation->id,
            ]));

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function user_cannot_update_another_users_reservation(): void
    {
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $otherUser->assignRole('user');

        $reservation = Reservation::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reservations.update-status', [
                'status' => 'cancelled',
                'id'     => $reservation->id,
            ]));

        // Status should not change
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'pending',
        ]);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // ADMIN RESERVATION MANAGEMENT
    // -------------------------------------------------------------------------

    /** @test */
    public function admin_can_update_reservation_status(): void
    {
        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.reservations.update-status'), [
                'id'     => $reservation->id,
                'status' => 'confirmed',
            ]);

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function admin_can_view_pending_reservations(): void
    {
        Reservation::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reservations.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertViewHas('reservations');
    }

    // -------------------------------------------------------------------------
    // VEHICLE SEARCH
    // -------------------------------------------------------------------------

    /** @test */
    public function vehicle_search_returns_json_results(): void
    {
        Vehicle::factory()->count(3)->create(['type' => 'car', 'is_available' => true]);

        $response = $this->getJson(route('reservations.search-vehicle', [
            'vehicle_type' => 'car',
            'q'            => '',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['items']);
    }

    /** @test */
    public function vehicle_search_filters_by_type(): void
    {
        Vehicle::factory()->count(2)->create(['type' => 'car']);
        Vehicle::factory()->count(2)->create(['type' => 'motorcycle']);

        $response = $this->getJson(route('reservations.search-vehicle', [
            'vehicle_type' => 'motorcycle',
        ]));

        $response->assertStatus(200);
        $data = $response->json('items');

        // All returned vehicles should be motorcycles
        foreach ($data as $item) {
            $this->assertEquals('motorcycle', $item['type'] ?? 'motorcycle');
        }
    }
}