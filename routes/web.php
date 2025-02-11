<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RentalController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RentalReportController;
use App\Http\Controllers\User\RentalsController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\VehicleController;
use App\Http\Controllers\Frontend\ReservationController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;
use App\Http\Controllers\User\ReservationController as UserReservationController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;

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

Auth::routes(['verify' => true]);

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
Route::get('/vehicle/reservations', [ReservationController::class, 'searchVehicle'])->name('reservations.search-vehicle');
Route::get('reservations/{status}/{id}/', [ReservationController::class, 'updateStatus'])->name('reservations.update-status');

// Payment Routes
Route::get('/payments/{reservation_id}', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/midtrans/notification', [PaymentController::class, 'notificationHandler'])->name('midtrans.notification');


Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

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
        'create' => 'admin.rentals.create',
        'store' => 'admin.rentals.store',
        'show' => 'admin.rentals.show',
        'edit' => 'admin.rentals.edit',
        'update' => 'admin.rentals.update',
        'destroy' => 'admin.rentals.destroy',
    ]);
    Route::get('rentals/index/{status}', [RentalController::class, 'index'])->name('admin.rentals.index');
    Route::post('rentals/status', [RentalController::class, 'updateStatus'])->name('admin.rentals.update-status');
    

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
    Route::post('reservations/status', [AdminReservationController::class, 'updateStatus'])->name('admin.reservations.update-status');

    Route::resource('users', UsersController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    Route::get('/admin/rentals/index', [RentalReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/rentals/export', [RentalReportController::class, 'export'])->name('admin.reports.export');

});


// User Dashboard
Route::prefix('user')->middleware(['auth', 'role:user', 'verified'])->group(callback: function () {
    Route::get('/dashboard', [DashboardController::class, 'indexUser'])->name('user.dashboard');

    Route::resource('reservations', UserReservationController::class)->names([
        'create' => 'user.reservations.create',
        'store' => 'user.reservations.store',
        'show' => 'user.reservations.show',
        'edit' => 'user.reservations.edit',
        'update' => 'user.reservations.update',
        'destroy' => 'user.reservations.destroy',
    ]);
    Route::get('reservations', [UserReservationController::class, 'index'])->name('user.reservations.index');
    Route::post('reservations/status', [UserReservationController::class, 'updateStatus'])->name('user.reservations.update-status');

    // Rental Routes
    Route::resource('rentals', RentalsController::class)->names([
        'create' => 'user.rentals.create',
        'store' => 'user.rentals.store',
        'show' => 'user.rentals.show',
        'edit' => 'user.rentals.edit',
        'update' => 'user.rentals.update',
        'destroy' => 'user.rentals.destroy',
    ]);
    Route::get('rentals', [RentalsController::class, 'index'])->name('user.rentals.index');
    Route::post('rentals/status', [RentalsController::class, 'updateStatus'])->name('user.rentals.update-status');
    
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Auth::routes();

