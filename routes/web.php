<?php

use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\VehicleController;
use App\Http\Controllers\Frontend\ReservationController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Frontend Home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Vehicle Routes
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->name('vehicles.show');

// Reservation Routes
Route::get('/reservations/create/{id?}', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

// Payment Routes
Route::get('/payments/{reservation_id}', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

// Review Routes
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Admin Dashboard
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Vehicle Routes
    Route::get('/api/checkbox-options', [AdminVehicleController::class, 'getOptions'])->name('admin.vehicles.checkbox');
    Route::resource('vehicles', AdminVehicleController::class)->names([
        'index' => 'admin.vehicles.index',
        'create' => 'admin.vehicles.create',
        'store' => 'admin.vehicles.store',
        'show' => 'admin.vehicles.show',
        'edit' => 'admin.vehicles.edit',
        'destroy' => 'admin.vehicles.destroy', 
    ]);
    Route::post('vehicles/update/{id}', [AdminVehicleController::class, 'update'])->name('admin.vehicles.update');

    // Rental Routes
    Route::resource('rentals', RentalController::class)->names([
        'index' => 'admin.rentals.index',
        'create' => 'admin.rentals.create',
        'store' => 'admin.rentals.store',
        'show' => 'admin.rentals.show',
        'edit' => 'admin.rentals.edit',
        'update' => 'admin.rentals.update',
        'destroy' => 'admin.rentals.destroy',
    ]);

    // Reservation Routes
    Route::resource('reservations', AdminReservationController::class)->names([
        'create' => 'admin.reservations.create',
        'store' => 'admin.reservations.store',
        'show' => 'admin.reservations.show',
        'edit' => 'admin.reservations.edit',
        'update' => 'admin.reservations.update',
        'destroy' => 'admin.reservations.destroy',
    ]);
    Route::get('reservations/index/{status}', [AdminReservationController::class, 'index'])->name('admin.reservations.index');

    // Review Routes
    Route::resource('reviews', AdminReviewController::class)->names([
        'index' => 'admin.reviews.index',
        'create' => 'admin.reviews.create',
        'store' => 'admin.reviews.store',
        'show' => 'admin.reviews.show',
        'edit' => 'admin.reviews.edit',
        'update' => 'admin.reviews.update',
        'destroy' => 'admin.reviews.destroy',
    ]);

    Route::resource('users', UsersController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    Route::resource('reports', ReportController::class)->names([
        'index' => 'admin.reports.index',
        'create' => 'admin.reports.create',
        'store' => 'admin.reports.store',
        'show' => 'admin.reports.show',
        'edit' => 'admin.reports.edit',
        'update' => 'admin.reports.update',
        'destroy' => 'admin.reports.destroy',
    ]);
});

// User Dashboard
Route::prefix('users')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'indexUser'])->name('user.dashboard');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
