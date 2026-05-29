<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected User $unverifiedUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'user',  'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->regularUser->assignRole('user');

        $this->unverifiedUser = User::factory()->unverified()->create();
        $this->unverifiedUser->assignRole('user');
    }

    // -------------------------------------------------------------------------
    // GUEST ACCESS
    // -------------------------------------------------------------------------

    /** @test */
    public function guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function guest_cannot_access_user_dashboard(): void
    {
        $response = $this->get(route('user.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function guest_can_access_home_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_can_access_vehicles_listing(): void
    {
        $response = $this->get(route('vehicles.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // ADMIN ROLE ACCESS
    // -------------------------------------------------------------------------

    /** @test */
    public function admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_vehicle_management(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.vehicles.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_reservations_management(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reservations.index', ['status' => 'pending']));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_rentals_management(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.rentals.index', ['status' => 'active']));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // USER ROLE BLOCKED FROM ADMIN
    // -------------------------------------------------------------------------

    /** @test */
    public function regular_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.dashboard'));

        // Should be forbidden or redirected — not 200
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_admin_vehicle_management(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.vehicles.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_admin_user_management(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // USER ROLE ACCESS (verified)
    // -------------------------------------------------------------------------

    /** @test */
    public function verified_user_can_access_user_dashboard(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function verified_user_can_access_their_reservations(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('user.reservations.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function verified_user_can_access_their_rentals(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('user.rentals.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // UNVERIFIED USER ACCESS
    // -------------------------------------------------------------------------

    /** @test */
    public function unverified_user_cannot_access_user_dashboard(): void
    {
        $response = $this->actingAs($this->unverifiedUser)
            ->get(route('user.dashboard'));

        // 'verified' middleware redirects to verification notice
        $response->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function unverified_user_cannot_access_their_reservations(): void
    {
        $response = $this->actingAs($this->unverifiedUser)
            ->get(route('user.reservations.index'));

        $response->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // ADMIN CANNOT ACCESS USER ROUTES
    // -------------------------------------------------------------------------

    /** @test */
    public function admin_cannot_access_user_dashboard(): void
    {
        // Admin has role:admin middleware on their routes,
        // user dashboard has role:user — admin should be blocked
        $response = $this->actingAs($this->adminUser)
            ->get(route('user.dashboard'));

        $response->assertStatus(403);
    }
}