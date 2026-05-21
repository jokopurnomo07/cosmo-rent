# Cosmo-rent — Bug Fix & Security Guide

This document covers every confirmed bug and security issue found in the codebase during a full static analysis. Each section includes: what the problem is, where to find it, and the exact code to fix it. Work through them top to bottom — the Critical issues first.

---

## Table of Contents

1. [Critical — Midtrans webhook uses wrong HTTP method](#1-critical--midtrans-webhook-uses-wrong-http-method)
2. [Critical — No Midtrans signature verification (fraud risk)](#2-critical--no-midtrans-signature-verification-fraud-risk)
3. [Critical — Missing CSRF exclusion for webhook](#3-critical--missing-csrf-exclusion-for-webhook)
4. [High — Reservation status update is unauthenticated](#4-high--reservation-status-update-is-unauthenticated)
5. [High — `handleFailure()` updates wrong model](#5-high--handlefailure-updates-wrong-model)
6. [High — APP_DEBUG and .env exposure](#6-high--app_debug-and-env-exposure)
7. [Medium — `dd()` left in production reservation store](#7-medium--dd-left-in-production-reservation-store)
8. [Medium — Paginator `.where()` misuse in admin reservation index](#8-medium--paginator-where-misuse-in-admin-reservation-index)
9. [Medium — `User\ReservationController` generates a new Midtrans transaction per page load](#9-medium--userreservationcontroller-generates-a-new-midtrans-transaction-per-page-load)
10. [Medium — Hardcoded Midtrans sandbox key fallback](#10-medium--hardcoded-midtrans-sandbox-key-fallback)
11. [Low — Double `Auth::routes()` registration](#11-low--double-authroutes-registration)
12. [Low — Unused variable in `createPayment()`](#12-low--unused-variable-in-createpayment)
13. [Environment checklist before going to production](#environment-checklist-before-going-to-production)

---

## 1. Critical — Midtrans webhook uses wrong HTTP method

**File:** `routes/web.php`

**Problem:** Midtrans sends payment notifications as HTTP `POST` requests. The route is registered as `Route::get(...)`. Every real payment notification Midtrans sends will receive a `405 Method Not Allowed` response and be ignored. No payments will ever be processed automatically.

**Find this line:**

```php
Route::get('/midtrans/notification', [PaymentController::class, 'notificationHandler'])->name('midtrans.notification');
```

**Replace with:**

```php
Route::post('/midtrans/notification', [PaymentController::class, 'notificationHandler'])->name('midtrans.notification');
```

> **Note:** After changing this, also update the `callbacks` finish URL inside `createPayment()` in both `Admin\ReservationController` and `User\ReservationController` — those currently point to `route('midtrans.notification')`. Midtrans's finish callback (the redirect after the user pays in the browser) is a GET redirect back to your site. You should separate the webhook URL (POST, server-to-server) from the finish redirect URL (GET, browser redirect). The simplest fix: add a separate GET route for the user to land on after payment:

```php
// routes/web.php

// Webhook — Midtrans server calls this after payment (server-to-server)
Route::post('/midtrans/notification', [PaymentController::class, 'notificationHandler'])
    ->name('midtrans.notification');

// Finish redirect — browser lands here after Midtrans payment page
Route::get('/payments/finish', [PaymentController::class, 'paymentFinish'])
    ->name('payment.finish');
```

Then in `PaymentController` add a simple finish method:

```php
public function paymentFinish(Request $request)
{
    return redirect()->route('home')->with('success', 'Pembayaran sedang diproses.');
}
```

And update both `createPayment()` methods to use `route('payment.finish')` for the `callbacks.finish` value.

---

## 2. Critical — No Midtrans signature verification (fraud risk)

**File:** `app/Http/Controllers/Frontend/PaymentController.php`

**Problem:** `notificationHandler()` reads `order_id` and `transaction_status` directly from the raw request without verifying the notification came from Midtrans. Anyone who knows (or guesses) a valid `trx_id` can send a fake POST to `/midtrans/notification` with `transaction_status=settlement` and the system will mark that reservation as paid and create a free rental record.

The `Midtrans\Notification` class — already imported at the top of the file — handles signature verification automatically. It is imported but never used.

**Current broken code:**

```php
public function notificationHandler(Request $request)
{
    $orderId = $request->order_id;
    $transactionStatus = $request->transaction_status;

    $reservation = Reservation::where('trx_id', $orderId)->with([...])->first();
    // ...
}
```

**Replace the entire method with:**

```php
public function notificationHandler(Request $request)
{
    try {
        // This verifies the signature_key against your server key automatically.
        // If tampered with, it throws an exception.
        $notification = new \Midtrans\Notification();

        $orderId           = $notification->order_id;
        $transactionStatus = $notification->transaction_status;

        $reservation = Reservation::where('trx_id', $orderId)
            ->with(['user:id,name,email,phone,address', 'services', 'vehicle'])
            ->first();

        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }

        match ($transactionStatus) {
            'capture', 'settlement' => $this->handleSuccess($reservation),
            'pending'               => $this->handlePending($reservation),
            'deny', 'expire', 'cancel' => $this->handleFailure($reservation),
        };

        // Midtrans expects a 200 response, not a redirect
        return response()->json(['status' => 'ok']);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Midtrans notification error: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid notification'], 400);
    }
}
```

Also fix `handleSuccess()` and `handlePending()` — they currently return a redirect/null but the caller ignores those return values. Remove the redirect from `handleSuccess()`:

```php
protected function handleSuccess($reservation)
{
    Reservation::where('trx_id', $reservation->trx_id)->update(['status' => 'paid']);

    $rental = Rental::updateOrCreate(
        ['trx_id' => $reservation->trx_id],
        [
            'user_id'           => $reservation->user_id,
            'vehicle_id'        => $reservation->vehicle_id,
            'rental_package_id' => $reservation->rental_package_id,
            'start_date'        => $reservation->start_date,
            'end_date'          => $reservation->end_date,
            'time_pickup'       => $reservation->time_pickup,
            'address_pickup'    => $reservation->address_pickup,
            'latitude'          => $reservation->latitude,
            'longitude'         => $reservation->longitude,
            'total_price'       => $reservation->total_price,
            'status'            => 'paid',
            'trx_id'            => $reservation->trx_id,
        ]
    );

    if ($reservation->vehicle->type === 'car' && $reservation->services->isNotEmpty()) {
        foreach ($reservation->services as $service) {
            RentalService::updateOrCreate([
                'rental_id'  => $rental->id,
                'service_id' => $service->id,
            ]);
        }
    }

    // No redirect here — this is called from a server-to-server webhook handler
}
```

---

## 3. Critical — Missing CSRF exclusion for webhook

**File:** `app/Http/Middleware/VerifyCsrfToken.php`

**Problem:** The Midtrans notification webhook is a POST from Midtrans's servers, not from a user browser session. It will not have a CSRF token. Laravel will reject it with a `419 Page Expired` before it even reaches the controller.

**Current state:**

```php
protected $except = [
    //
];
```

**Fix — add the webhook URL:**

```php
protected $except = [
    'midtrans/notification',
];
```

---

## 4. High — Reservation status update is unauthenticated

**File:** `routes/web.php` and `app/Http/Controllers/Frontend/ReservationController.php`

**Problem A — no auth middleware on the route:**

```php
// This route has no auth middleware
Route::get('reservations/{status}/{id}/', [ReservationController::class, 'updateStatus'])
    ->name('reservations.update-status');
```

Any anonymous visitor can hit `/reservations/canceled/1` in their browser and cancel reservation ID 1 — belonging to any user.

**Fix — add auth middleware:**

```php
Route::get('reservations/{status}/{id}/', [ReservationController::class, 'updateStatus'])
    ->middleware('auth')
    ->name('reservations.update-status');
```

**Problem B — no ownership check in the controller:**

```php
public function updateStatus($status, $id)
{
    $reservation = Reservation::find($id); // finds ANY reservation by ID
    // ...
}
```

**Fix — scope to the logged-in user:**

```php
public function updateStatus($status, $id)
{
    $reservation = Reservation::where('id', $id)
        ->where('user_id', auth()->id()) // user can only change their own reservations
        ->first();

    if (!$reservation) {
        return redirect()->route('home')->with('successCanceled', false);
    }

    $reservation->loadMissing(['user:id,name,email,phone,address', 'services', 'vehicle']);

    $reservation->status = $status;

    if ($status === 'canceled') {
        $reservation->reason_canceled = 'User telah membatalkan reservasi.';
        Mail::to($reservation->user->email)
            ->send(new ReservationRejectionNotification($reservation, 'Pembatalan'));
    }

    $reservation->save();

    return redirect()->route('home')->with('successCanceled', true);
}
```

> Also note: the original code has a variable `$status` that is shadowed when `$status` is reassigned to the string `"Penolakan"` or `"Pembatalan"` for the email, but the `$reservation->status` assignment already ran above. Review the logic carefully and keep the variable names distinct.

---

## 5. High — `handleFailure()` updates wrong model

**File:** `app/Http/Controllers/Frontend/PaymentController.php`

**Problem:** When a payment fails (`deny`, `expire`, or `cancel`), `handleFailure()` tries to update a `Rental` record — but `Rental` records are only created on *successful* payment (in `handleSuccess()`). At failure time, no Rental exists for this transaction. The update silently affects 0 rows. The Reservation is never marked as failed and stays `confirmed` forever.

**Current broken code:**

```php
protected function handleFailure($reservation)
{
    Rental::where('trx_id', $reservation->trx_id)->update(['status' => 'payment_failed']);
    return view('response_email.response_payment_failed');
}
```

**Fix — update the Reservation, not the Rental:**

```php
protected function handleFailure($reservation)
{
    Reservation::where('trx_id', $reservation->trx_id)
        ->update(['status' => 'expired']); // or add 'payment_failed' to the enum

    // If a Rental somehow exists (retry scenario), clean it up too
    Rental::where('trx_id', $reservation->trx_id)
        ->update(['status' => 'payment_failed']);
}
```

If you want to use `'payment_failed'` as a status, you need to add it to the enum in the migration. Add a new migration:

```php
// database/migrations/xxxx_add_payment_failed_to_reservations.php
Schema::table('reservations', function (Blueprint $table) {
    DB::statement("ALTER TABLE reservations DROP CONSTRAINT IF EXISTS reservations_status_check");
    DB::statement("ALTER TABLE reservations ADD CONSTRAINT reservations_status_check 
        CHECK (status IN ('pending','canceled','confirmed','expired','on_hold','rejected','paid','payment_failed'))");
});
```

Or simply use `'expired'` which is already in the enum.

---

## 6. High — APP_DEBUG and .env exposure

**File:** `.env`

**Problem 1 — `APP_DEBUG=true`:** In production this exposes full stack traces — including file paths, environment variables, and SQL queries — to any user who triggers an error.

**Problem 2 — `.env` committed to git:** The `.env` file exists in your zip, which means it was tracked by git at some point (confirmed: `.git/index` is present). The `APP_KEY` and any credentials in it may be in git history.

**Fix `.env` for production:**

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=         <-- generate a new one: php artisan key:generate
```

**Fix git history exposure:**

```bash
# Check if .env is tracked
git ls-files .env

# If it shows up, remove it from tracking
git rm --cached .env
echo ".env" >> .gitignore
git commit -m "Remove .env from tracking"

# Rotate the APP_KEY since it may be in history
php artisan key:generate
```

**Rotate any credentials that were in the exposed .env**, including database passwords.

---

## 7. Medium — `dd()` left in production reservation store

**File:** `app/Http/Controllers/Frontend/ReservationController.php`

**Problem:** If an exception is thrown during reservation creation, `dd($e->getMessage())` dumps the error and halts — the redirect below it never runs. In production this shows a raw dump to the user and potentially leaks internal details.

**Current broken code:**

```php
} catch (\Exception $e) {
    DB::rollBack();
    dd($e->getMessage()); // <-- remove this
    return redirect()->back()->with('error', 'Failed to add reservation. Please try again.');
}
```

**Fix:**

```php
} catch (\Exception $e) {
    DB::rollBack();
    \Illuminate\Support\Facades\Log::error('Reservation store failed: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
    ]);
    return redirect()->back()->with('error', 'Gagal membuat reservasi. Silakan coba lagi.');
}
```

---

## 8. Medium — Paginator `.where()` misuse in admin reservation index

**File:** `app/Http/Controllers/Admin/ReservationController.php`

**Problem:** The result of `->paginate()` is a `LengthAwarePaginator` object, not an Eloquent query builder. Calling `.where()` on a paginator does nothing — it returns an empty collection. The `services` relationship will never be loaded for any reservation in the admin list.

**Current broken code:**

```php
$reservation = Reservation::with(['user', 'vehicle', 'rental_package'])
    ->whereIn('status', $status)
    ->latest()
    ->paginate(10);

$reservation->where('type', 'car')->load('services'); // BUG: this does nothing
```

**Fix — load services inside the original query using a conditional eager load:**

```php
$reservation = Reservation::with([
        'user',
        'vehicle',
        'rental_package',
        'services' => function ($query) {
            // services are only relevant for cars, but loading for all is harmless
        },
    ])
    ->whereIn('status', $status)
    ->latest()
    ->paginate(10);
```

If you only want services for car reservations (to save queries), use `whenLoaded` in the blade template and let the relationship load for everything — it's more reliable than filtering after pagination.

---

## 9. Medium — `User\ReservationController` generates a new Midtrans transaction per page load

**File:** `app/Http/Controllers/User/ReservationController.php`

**Problem:** `index()` loops through every reservation and calls `createPayment()` for each one. `createPayment()` calls `Snap::createTransaction()` on the Midtrans API — a real HTTP request — for every reservation every time the page loads. With 10 reservations on the page, that's 10 external API calls per page load. This will:
- Slow the page down significantly
- Hit Midtrans rate limits
- Create duplicate Snap tokens for the same reservation on every visit

**Current broken code:**

```php
foreach ($reservations as $reservation) {
    $reservation->payment_url = $this->createPayment($reservation); // API call per reservation
}
```

**Fix — only generate a payment URL for reservations that need it, and store/cache the URL:**

The best fix is to store the Midtrans `redirect_url` on the reservation when it's first generated (when the admin confirms the reservation and emails the customer). The URL is already being generated in `Admin\ReservationController::updateStatus()` and emailed. Store it:

**Step 1 — add a `payment_url` column to reservations:**

```bash
php artisan make:migration add_payment_url_to_reservations_table
```

```php
// In the migration
Schema::table('reservations', function (Blueprint $table) {
    $table->string('payment_url')->nullable()->after('trx_id');
});
```

**Step 2 — save the URL when the admin confirms:**

In `Admin\ReservationController::updateStatus()`, after generating the URL:

```php
if ($request->status === 'confirmed') {
    $paymentUrl = $this->createPayment($reservation);
    $reservation->payment_url = $paymentUrl; // store it
    $reservation->save();
    Mail::to(...)->send(new VehicleAvailabilityNotification($reservation, $paymentUrl));
}
```

**Step 3 — use the stored URL in `User\ReservationController::index()`:**

```php
public function index()
{
    $reservations = Reservation::with(['vehicle', 'rental_package', 'services'])
        ->where('user_id', auth()->id())
        ->latest()
        ->paginate(10);

    // No API calls — just use the stored URL
    $notifications = Notification::where('is_read', false)->latest()->paginate(10);

    return view('user.reservation.index', compact('reservations', 'notifications'));
}
```

Remove the `createPayment()` method from `User\ReservationController` entirely.

---

## 10. Medium — Hardcoded Midtrans sandbox key fallback

**File:** `app/Providers/AppServiceProvider.php`

**Problem:** The server key has a hardcoded sandbox key as a default fallback value:

```php
Config::$serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-deL7LiPsBalCRdIg0AsiWpzo');
```

If `MIDTRANS_SERVER_KEY` is not set in `.env`, the app silently uses this hardcoded key. Anyone reading the source code can use it. Hardcoded credentials in source code are a security anti-pattern regardless of environment.

**Fix — remove the fallback default and fail loudly if the key is missing:**

```php
public function boot(): void
{
    $serverKey = env('MIDTRANS_SERVER_KEY');

    if (empty($serverKey)) {
        throw new \RuntimeException('MIDTRANS_SERVER_KEY is not set in .env');
    }

    Config::$serverKey    = $serverKey;
    Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    Config::$isSanitized  = true;
    Config::$is3ds        = true;

    require_once base_path('app/Helpers/KagenouHelper.php');

    if (env('APP_ENV') !== 'local') {
        URL::forceScheme('https');
    }
}
```

Add to your `.env`:

```
MIDTRANS_SERVER_KEY=SB-Mid-server-your-actual-key-here
MIDTRANS_IS_PRODUCTION=false
```

---

## 11. Low — Double `Auth::routes()` registration

**File:** `routes/web.php`

**Problem:** `Auth::routes(['verify' => true])` is called at the top of the file, and `Auth::routes()` is called again at the bottom (without the verify option). This registers all authentication routes twice. Laravel won't throw an error, but it wastes boot time and the second registration silently overwrites the `verify` configuration.

**Fix — keep only one call, at the top, and delete the duplicate at the bottom:**

Keep this at the top:
```php
Auth::routes(['verify' => true]);
```

Delete this at the bottom:
```php
Auth::routes(); // <-- remove this line
```

---

## 12. Low — Unused variable in `createPayment()`

**File:** `app/Http/Controllers/Admin/ReservationController.php`

**Problem:** Inside `createPayment()`, `$orderId = uniqid()` is generated and immediately ignored. The actual transaction uses `$reservation->trx_id`. The unused variable suggests this was copied from a template without cleanup.

**Current code:**

```php
public function createPayment($reservation)
{
    DB::beginTransaction();
    try {
        $orderId = uniqid(); // <-- never used
        $amount = $reservation->total_price;

        $params = [
            'transaction_details' => [
                'order_id' => $reservation->trx_id, // uses trx_id, not $orderId
```

**Fix — remove the unused variable:**

```php
public function createPayment($reservation)
{
    DB::beginTransaction();
    try {
        $amount = $reservation->total_price;

        $params = [
            'transaction_details' => [
                'order_id'     => $reservation->trx_id,
                'gross_amount' => $amount,
            ],
```

Also remove the `DB::beginTransaction()` / `DB::commit()` from this method. `createPayment()` only calls the Midtrans API — it doesn't write to the database. Wrapping an external API call in a DB transaction does nothing useful and is misleading.

---

## Environment checklist before going to production

Go through this list before deploying.

### `.env` settings

| Variable | Required value in production |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Rotate — generate a new one with `php artisan key:generate` |
| `APP_URL` | Your actual HTTPS domain, e.g. `https://cosmorent.id` |
| `DB_PASSWORD` | Not `root` — use a strong password |
| `MIDTRANS_SERVER_KEY` | Your **production** server key from Midtrans dashboard |
| `MIDTRANS_IS_PRODUCTION` | `true` |
| `MAIL_FROM_ADDRESS` | Your real sender address, not `hello@example.com` |

### Midtrans dashboard settings

- Set your **Production Notification URL** to `https://yourdomain.com/midtrans/notification` (POST)
- Enable **3DS** on all payment methods
- Set **Finish, Unfinish, Error** redirect URLs to your finish redirect route

### Git

```bash
# Make sure .env is not tracked
git ls-files .env
# Should return nothing. If it returns ".env", run:
git rm --cached .env && git commit -m "chore: remove .env from git"
```

### Final commands before deploy

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```