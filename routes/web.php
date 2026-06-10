<?php

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
use App\Http\Controllers\User\BookingHistoryController;
use App\Http\Controllers\Frontend\RentalExtensionController;
use App\Http\Controllers\Admin\RentalExtensionController as AdminRentalExtensionController;
use App\Http\Controllers\Admin\RentalTrackingController;

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
Route::get('reservations/{status}/{id}/', [ReservationController::class, 'updateStatus'])
    ->middleware('auth')
    ->name('reservations.update-status');

// Payment Routes
Route::get('/payments/finish', [PaymentController::class, 'paymentFinish'])->name('payment.finish');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::post('/midtrans/notification', [PaymentController::class, 'notificationHandler'])->name('midtrans.notification');
Route::get('/payments/{reservation_id}', [PaymentController::class, 'create'])->name('payments.create');

// Public Tracking Endpoint for device/webhooks (uses TRACKING_SECRET token header 'X-TRACKING-TOKEN')
Route::post('/tracking/{rental_id}/add-location', [RentalTrackingController::class, 'addLocationPublic'])->name('tracking.add-location.public');


// Extension Finish Payment (after user completes Midtrans payment)
Route::get('/extensions/finish', [RentalExtensionController::class, 'finish'])->name('extension.finish');

// Notification Routes
Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
Route::post('/notifications/toggle-read',   [NotificationController::class, 'toggleRead'])->name('notifications.toggle-read');
Route::post('/notifications/mark-unread',   [NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');

// ─────────────────────────────────────────────────────────────────────────────
// Admin Dashboard
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');


    // ── Vehicle Routes ────────────────────────────────────────────────────────
    Route::get('/api/checkbox-options', [AdminVehicleController::class, 'getOptions'])->name('admin.vehicles.checkbox');
    Route::post('vehicles/update/{id}', [AdminVehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::resource('vehicles', AdminVehicleController::class)->names([
        'index'   => 'admin.vehicles.index',
        'create'  => 'admin.vehicles.create',
        'store'   => 'admin.vehicles.store',
        'show'    => 'admin.vehicles.show',
        'edit'    => 'admin.vehicles.edit',
        'destroy' => 'admin.vehicles.destroy',
    ]);

    // ── Rental Routes ─────────────────────────────────────────────────────────
    Route::get('rentals/index/{status}', [RentalController::class, 'index'])->name('admin.rentals.index');
    Route::post('rentals/status', [RentalController::class, 'updateStatus'])->name('admin.rentals.update-status');
    Route::resource('rentals', RentalController::class)->names([
        'create'  => 'admin.rentals.create',
        'store'   => 'admin.rentals.store',
        'show'    => 'admin.rentals.show',
        'edit'    => 'admin.rentals.edit',
        'update'  => 'admin.rentals.update',
        'destroy' => 'admin.rentals.destroy',
    ]);

    // ── Reservation Routes ────────────────────────────────────────────────────
    //
    // PENTING: Route AJAX (search-vehicle, search-user) HARUS didaftarkan
    // SEBELUM Route::resource agar Laravel tidak salah menginterpretasikan
    // string "search-vehicle" sebagai parameter {reservation} dari resource.
    //
    Route::get('reservations/index/{status}', [AdminReservationController::class, 'index'])->name('admin.reservations.index');
    Route::post('reservations/status', [AdminReservationController::class, 'updateStatus'])->name('admin.reservations.update-status');

    // ↓↓ Dua route ini BARU — harus ada sebelum Route::resource ↓↓
    Route::get('reservations/search-vehicle', [AdminReservationController::class, 'searchVehicle'])->name('admin.reservations.search-vehicle');
    Route::get('reservations/search-user', [AdminReservationController::class, 'searchUser'])->name('admin.reservations.search-user');

    Route::resource('reservations', AdminReservationController::class)->names([
        'create'  => 'admin.reservations.create',
        'store'   => 'admin.reservations.store',
        'show'    => 'admin.reservations.show',
        'edit'    => 'admin.reservations.edit',
        'update'  => 'admin.reservations.update',
        'destroy' => 'admin.reservations.destroy',
    ]);

    // ── User Routes ───────────────────────────────────────────────────────────
    Route::resource('users', UsersController::class)->names([
        'index'   => 'admin.users.index',
        'create'  => 'admin.users.create',
        'store'   => 'admin.users.store',
        'show'    => 'admin.users.show',
        'edit'    => 'admin.users.edit',
        'update'  => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    // ── Report Routes ─────────────────────────────────────────────────────────
    Route::get('/reports', [RentalReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export', [RentalReportController::class, 'export'])->name('admin.reports.export');

    // ── Rental Extension Routes ───────────────────────────────────────────────
    Route::get('extensions/index/{status?}', [AdminRentalExtensionController::class, 'index'])->name('admin.extensions.index');
    Route::get('extensions/{id}', [AdminRentalExtensionController::class, 'show'])->name('admin.extensions.show');
    Route::post('extensions/{id}/approve', [AdminRentalExtensionController::class, 'approve'])->name('admin.extensions.approve');
    Route::post('extensions/{id}/reject', [AdminRentalExtensionController::class, 'reject'])->name('admin.extensions.reject');
    // Simulation removed in production: admin only approves now. No simulate-payment route.

    // ── Rental Tracking Routes ────────────────────────────────────────────────
    Route::get('tracking', [RentalTrackingController::class, 'index'])->name('admin.tracking.index');
    Route::get('tracking/{rental_id}', [RentalTrackingController::class, 'show'])->name('admin.tracking.show');
    Route::get('tracking/{rental_id}/current-location', [RentalTrackingController::class, 'getCurrentLocation'])->name('admin.tracking.current-location');
    Route::get('tracking/{rental_id}/history', [RentalTrackingController::class, 'getLocationHistory'])->name('admin.tracking.history');
    Route::post('tracking/{rental_id}/demo', [RentalTrackingController::class, 'generateDemoLocations'])->name('admin.tracking.demo');
    Route::post('tracking/{rental_id}/add-location', [RentalTrackingController::class, 'addLocation'])->name('admin.tracking.add-location');
});

// ─────────────────────────────────────────────────────────────────────────────
// User Dashboard
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('user')->middleware(['auth', 'role:user', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'indexUser'])->name('user.dashboard');

    Route::get('history', [BookingHistoryController::class, 'index'])->name('user.history.index');

    Route::post('reservations/status', [UserReservationController::class, 'updateStatus'])->name('user.reservations.update-status');
    Route::resource('reservations', UserReservationController::class)->names([
        'index'   => 'user.reservations.index',
        'create'  => 'user.reservations.create',
        'store'   => 'user.reservations.store',
        'show'    => 'user.reservations.show',
        'edit'    => 'user.reservations.edit',
        'update'  => 'user.reservations.update',
        'destroy' => 'user.reservations.destroy',
    ]);

    Route::post('rentals/status', [RentalsController::class, 'updateStatus'])->name('user.rentals.update-status');
    Route::resource('rentals', RentalsController::class)->names([
        'index'   => 'user.rentals.index',
        'create'  => 'user.rentals.create',
        'store'   => 'user.rentals.store',
        'show'    => 'user.rentals.show',
        'edit'    => 'user.rentals.edit',
        'update'  => 'user.rentals.update',
        'destroy' => 'user.rentals.destroy',
    ]);

        // Debug: Midtrans transaction creation for an extension (LOCAL ONLY)
        Route::get('debug/extensions/{id}/midtrans', [\App\Http\Controllers\Frontend\RentalExtensionController::class, 'debugMidtrans'])->name('debug.extensions.midtrans');

    // ── Rental Extension Routes ───────────────────────────────────────
    Route::get('rentals/{rental_id}/extend', [RentalExtensionController::class, 'create'])->name('user.extensions.create');
    Route::post('rentals/{rental_id}/extend', [RentalExtensionController::class, 'store'])->name('user.extensions.store');
    Route::get('extensions', [RentalExtensionController::class, 'index'])->name('user.extensions.index');
    Route::match(['get','post'],'extensions/{id}/pay', [RentalExtensionController::class, 'pay'])->name('user.extensions.pay');
});

// ─────────────────────────────────────────────────────────────────────────────
// Profile Routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

require __DIR__.'/auth.php';