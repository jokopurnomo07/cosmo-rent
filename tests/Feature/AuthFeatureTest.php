<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed roles before each test since Spatie permissions
     * requires roles to exist in the DB.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'user',  'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
    }

    // -------------------------------------------------------------------------
    // REGISTRATION
    // -------------------------------------------------------------------------

    /** @test */
    public function registration_page_is_accessible_to_guests(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // User exists in DB
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);

        // Role assigned
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('user'));

        // Registered event was fired (triggers verification email)
        Event::assertDispatched(Registered::class);

        // Redirected to login with success flag
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function registration_requires_name(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => '',
            'email'                 => 'john@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->post(route('register'), [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function registration_requires_matching_passwords(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function authenticated_user_cannot_access_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));

        // Should redirect away (guest middleware)
        $response->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------------------------

    /** @test */
    public function login_page_is_accessible_to_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'john@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    /** @test */
    public function user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'john@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->post(route('login'), [
            'email'    => 'john@example.com',
            'password' => 'WrongPassword!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post(route('login'), [
            'email'    => 'ghost@example.com',
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // -------------------------------------------------------------------------
    // EMAIL VERIFICATION
    // -------------------------------------------------------------------------

    /** @test */
    public function unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('user');

        // User routes require 'verified' middleware
        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function verified_user_can_access_user_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $response->assertRedirect();
    }

    /** @test */
    public function user_cannot_verify_email_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertNull($user->fresh()->email_verified_at);
        $response->assertStatus(403);
    }

    /** @test */
    public function verification_notice_page_loads_for_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
    }
}