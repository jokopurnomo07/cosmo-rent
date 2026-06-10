<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\RentalPackage;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Http\Controllers\Frontend\PaymentController;
use Carbon\Carbon;

class ExtensionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_extension_success_updates_rental()
    {
        // Arrange: create user, vehicle, package, rental and an approved extension
        $user = User::factory()->create();

        $vehicle = Vehicle::create([
            'name' => 'Test Car',
            'type' => 'car',
            'brand' => 'Brand',
            'model' => 'Model',
            'year' => 2020,
            'transmission' => 'auto',
            'fuel' => 'petrol',
            'registration_number' => 'ABC123'.time(),
            'capacity' => 4,
        ]);

        $package = RentalPackage::create([
            'name' => 'Daily',
            'duration_hours' => 24,
        ]);

        $rental = Rental::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'rental_package_id' => $package->id,
            'trx_id' => 'TRX-' . time(),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(1),
            'status' => 'ongoing',
        ]);

        $newEnd = Carbon::now()->addDays(3);

        $extension = RentalExtension::create([
            'rental_id' => $rental->id,
            'extended_until' => $newEnd,
            'additional_price' => 100000,
            'status' => 'approved',
        ]);

        // Act: call the protected handler via reflection to simulate webhook success
        $controller = new PaymentController();
        $method = new \ReflectionMethod($controller, 'handleExtensionSuccess');
        $method->setAccessible(true);
        $method->invoke($controller, $extension);

        // Refresh models
        $extension->refresh();
        $rental->refresh();

        // Assert
        $this->assertEquals('paid', $extension->status);
        $this->assertEquals($newEnd->format('Y-m-d H:i'), Carbon::parse($rental->end_date)->format('Y-m-d H:i'));
    }
}
