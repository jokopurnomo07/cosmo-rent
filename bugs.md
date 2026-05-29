# Reservation System — Audit & Gap Analysis

> Berdasarkan review kode: `Frontend/ReservationController`, `Admin/ReservationController`,
> `User/ReservationController`, `Console/Kernel`, `NotifyUsersForPickup`, `Reservation` model,
> `PaymentController`, `RentalController`, `routes/web.php`, `routes/auth.php`,
> `VerifyCsrfToken`, migration `reservations`, dan view terkait.
>
> **Versi dokumen:** 3.0 — diperbarui setelah review VerifyCsrfToken, RentalController, auth routes, dan rental show view

---

## 1. Arsitektur Sistem

### 1.1 Dua Model Utama: Reservation vs Rental

| | `Reservation` | `Rental` |
|---|---|---|
| **Artinya** | Pemesanan kendaraan oleh user | Aktif setelah pembayaran berhasil |
| **Dibuat oleh** | User via form pemesanan | Sistem otomatis saat webhook `settlement` |
| **Status awal** | `pending` | `paid` |
| **Tujuan** | Tracking proses booking | Tracking proses sewa aktif |

### 1.2 Flow Lengkap

```
[User buat reservasi]
        ↓
   Reservation: pending
        ↓ (admin konfirmasi)
   Reservation: confirmed + payment_url dibuat via Midtrans Snap
        ↓ (user bayar)
   Webhook settlement → Reservation: paid + Rental dibuat otomatis
   Webhook expire/deny/cancel → Reservation: ❌ error (status tidak ada di enum)
        ↓ (admin proses rental)
   Rental: paid → ongoing → returned
```

### 1.3 Status Enum Reservasi (dari migration)

| Status | Artinya | Diset Oleh |
|---|---|---|
| `pending` | Menunggu konfirmasi admin | Default saat dibuat |
| `confirmed` | Dikonfirmasi, menunggu bayar | Admin |
| `paid` | Pembayaran berhasil | Webhook Midtrans |
| `expired` | Kadaluarsa — ada di enum tapi belum dipakai | — |
| `on_hold` | Ditunda — ada di enum tapi belum dipakai | — |
| `canceled` | Dibatalkan user atau admin | User / Admin |
| `rejected` | Ditolak admin | Admin |

### 1.4 Status Enum Rental (dari RentalController)

| Status | Artinya |
|---|---|
| `paid` | Pembayaran diterima, menunggu diproses admin |
| `ongoing` | Kendaraan sedang digunakan |
| `returned` | Kendaraan sudah dikembalikan |

---

## 2. Yang Sudah Ada ✅

### 2.1 Flow Reservasi Dasar
- [x] User bisa membuat reservasi (`Frontend/ReservationController@store`)
- [x] Harga dihitung otomatis dari `VehiclePrice` berdasarkan durasi paket rental
- [x] Data user (phone, address) diupdate otomatis saat reservasi dibuat
- [x] `trx_id` unik digenerate otomatis via helper `generateUniqueID()`
- [x] Support layanan tambahan (`ReservationService`) untuk tipe kendaraan `car`
- [x] Event `ReservationCreated` di-fire setelah reservasi dibuat
- [x] DB transaction dengan rollback jika gagal
- [x] Default status `pending` sudah di-set di level database

### 2.2 Admin — Reservation Management
- [x] Admin bisa lihat reservasi berdasarkan status (pending / confirmed / canceled+rejected)
- [x] Admin bisa konfirmasi, tolak, atau batalkan reservasi
- [x] Saat admin konfirmasi → Midtrans Snap payment URL dibuat otomatis
- [x] `payment_url` disimpan dan dikirim via email ke user

### 2.3 Admin — Rental Management
- [x] Admin bisa lihat rental berdasarkan status (`paid`, `ongoing`, `returned`)
- [x] Admin bisa update status rental (`updateStatus`)
- [x] Detail rental bisa dilihat via `show()` — return view `admin.rentals.show`
- [x] Rental load relasi: `user`, `vehicle`, `rental_package`, `services` (untuk tipe car)

### 2.4 Webhook Midtrans
- [x] Route webhook sudah ada: `POST /midtrans/notification`
- [x] Route sudah exempt dari CSRF di `VerifyCsrfToken.php` ✅ (sudah benar)
- [x] Handler `settlement`/`capture` → status `paid` + buat `Rental` otomatis
- [x] Saat payment berhasil, services dari Reservation di-copy ke Rental via `updateOrCreate`

### 2.5 Email Notifikasi
- [x] Email konfirmasi reservasi (`VehicleReservationConfirmation`)
- [x] Email pembatalan/penolakan (`ReservationRejectionNotification`)
- [x] Email link pembayaran (`VehicleAvailabilityNotification`)
- [x] Email notifikasi kendaraan siap pickup (`VehicleReadyForPickup`)

### 2.6 User Side
- [x] User bisa lihat list reservasinya
- [x] User bisa lihat detail reservasi via modal AJAX
- [x] User bisa batalkan reservasi sendiri
- [x] Tombol "Pay Now" muncul untuk reservasi berstatus `confirmed`

### 2.7 Scheduled Task
- [x] `notify:pickup` — berjalan setiap hari, kirim email ke user yang kendaraannya siap diambil hari itu (query dari `Rental` — sudah benar)

### 2.8 Auth
- [x] Auth controller yang aktif sudah jelas dari `routes/auth.php`: pakai `RegisteredUserController` dan `AuthenticatedSessionController` (Breeze)
- [x] Controller lama (`LoginController`, `RegisterController`, dll) tidak dipakai di routes — dead code
- [x] Email verification flow sudah ada, termasuk polling `check-verification` dan halaman `email-verified`

### 2.9 Activity Log
- [x] Model `Reservation` menggunakan `spatie/laravel-activitylog`
- [x] Field yang di-log: `user_id`, `vehicle_id`, `start_date`, `end_date`, `total_price`, `status`

---

## 3. Bug & Gap ❌

### 3.1 🔴 KRITIS — Status `failed` dan `payment_pending` Tidak Ada di Enum

**Lokasi:** `PaymentController@handleFailure` dan `handlePending`

```php
// handleFailure menulis:
->update(['status' => 'failed']);          // ❌ tidak ada di enum

// handlePending menulis:
->update(['status' => 'payment_pending']); // ❌ tidak ada di enum
```

Enum migration hanya punya: `pending`, `canceled`, `confirmed`, `expired`, `on_hold`, `rejected`, `paid`

**Dampak:** Database throw error saat webhook `expire`/`deny`/`cancel`/`pending` diterima. Webhook return 500, Midtrans akan retry berkali-kali, status tidak pernah terupdate.

**Fix pilihan:**
- Tambah `failed` dan `payment_pending` ke enum via migration baru, ATAU
- Ubah `handleFailure` pakai `expired` dan `handlePending` biarkan `pending` (tidak ubah status)

---

### 3.2 🔴 KRITIS — Tidak Ada Signature Verification di Webhook

**Lokasi:** `PaymentController@notificationHandler`

Endpoint bisa dipanggil siapapun tanpa verifikasi keaslian request dari Midtrans.

**Dampak:** Pihak luar bisa kirim request palsu untuk mengubah status reservasi menjadi `paid` tanpa bayar.

**Catatan:** `new \Midtrans\Notification()` mungkin sudah auto-verify tergantung versi library dan konfigurasi `isValidSignatureKey`. Perlu dicek versi midtrans-php yang dipakai.

**Fix jika belum ada:**
```php
$serverKey    = config('midtrans.server_key');
$signatureKey = hash('sha512',
    $notification->order_id .
    $notification->status_code .
    $notification->gross_amount .
    $serverKey
);
if ($signatureKey !== $notification->signature_key) {
    return response()->json(['error' => 'Invalid signature'], 403);
}
```

---

### 3.3 🔴 KRITIS — Tidak Ada Mekanisme Expired Order (Pending Timeout)

Status `expired` sudah ada di enum tapi tidak dipakai oleh apapun.

- [ ] Tidak ada kolom `order_expired_at` di tabel reservasi
- [ ] Reservasi `pending` yang tidak dikonfirmasi admin bisa bertahan selamanya
- [ ] Tidak ada Artisan command untuk auto-expire reservasi pending
- [ ] Tidak ada schedule yang menjalankan expiry check

**Yang dibutuhkan:**
1. Migration: tambah kolom `order_expired_at` (diset saat reservasi dibuat, misal +24 jam)
2. Set nilai `order_expired_at` di `Frontend/ReservationController@store`
3. Artisan command `reservations:expire-pending` — ubah `pending` → `expired` jika sudah lewat
4. Schedule command tersebut tiap 5–15 menit di `Kernel.php`

---

### 3.4 🟠 TINGGI — Filter Status Tidak Berfungsi di User Controller

**Lokasi:** `User/ReservationController@index`

Query tidak memfilter berdasarkan `request('status')`, padahal view sudah pakai parameter itu untuk judul dan kolom kondisional. Semua tab menampilkan data yang sama.

**Fix:**
```php
$reservations = Reservation::with([...])
    ->where('user_id', auth()->id())
    ->when(request('status') == 'pending', fn($q) => $q->whereIn('status', ['pending']))
    ->when(request('status') == 'canceled', fn($q) => $q->whereIn('status', ['canceled', 'rejected']))
    ->when(!request('status'), fn($q) => $q->whereIn('status', ['confirmed', 'paid']))
    ->latest()
    ->paginate(10);
```

---

### 3.5 🟠 TINGGI — `show()` User Reservation Return Full Layout, Dipanggil via AJAX

**Lokasi:** `User/ReservationController@show`

View yang di-return kemungkinan extend layout penuh, tapi dipanggil via AJAX dan di-inject ke modal. Perlu cek view `user.reservation.show` — apakah sudah partial atau masih extend layout.

---

### 3.6 🟠 TINGGI — Bug di RentalController: `->where()` Dipanggil Setelah `->paginate()`

**Lokasi:** `Admin/RentalController@index`

```php
$rentals = Rental::whereIn('status', $status)->orderBy(...)->paginate(10);
$rentals->loadMissing([...]); // OK
$rentals->where('type', 'car')->load('services'); // ❌ SALAH
```

`$rentals` setelah `paginate()` adalah `LengthAwarePaginator`, bukan Builder. Memanggil `->where()` di atasnya tidak valid — ini akan error atau tidak berfungsi sama sekali. Services untuk rental tipe `car` tidak akan pernah ter-load.

**Dampak:** Di halaman detail rental mobil, kolom "Layanan" akan kosong atau error.

**Fix:**
```php
$rentals = Rental::whereIn('status', $status)
    ->with(['user:id,name,email,phone,address', 'vehicle', 'rental_package'])
    ->orderBy('created_at', 'DESC')
    ->paginate(10);

// Load services hanya untuk yang tipe car, setelah paginate
$rentals->getCollection()->each(function ($rental) {
    if ($rental->vehicle && $rental->vehicle->type === 'car') {
        $rental->loadMissing('services');
    }
});
```

---

### 3.7 🟠 TINGGI — Rental Show View: Akses `$rentals->services[0]` Tanpa Pengecekan

**Lokasi:** View `admin.rentals.show`

```blade
@if ($rentals->vehicle->type == "car")
<tr>
    <td>Layanan</td>
    <td>{{ $rentals->services[0]->name }}</td>  {{-- ❌ tidak ada pengecekan --}}
</tr>
@endif
```

Jika rental tipe `car` tapi tidak punya service (data kosong atau gagal load), ini akan throw `ErrorException: Trying to get property of non-object` atau `Undefined offset: 0`.

**Fix:**
```blade
<td>{{ $rentals->services->first()?->name ?? '-' }}</td>
```

---

### 3.8 🟡 MEDIUM — Notifikasi Tidak Di-scope ke User/Role

**Lokasi:** `User/ReservationController@index` dan `Admin/ReservationController@index` dan `Admin/RentalController@index`

```php
$notifications = Notification::where('is_read', false)->latest()->paginate(10);
```

Tidak ada filter `user_id` atau role. Semua notifikasi unread dari semua user di-load di setiap halaman.

---

### 3.9 🟡 MEDIUM — Potensi Konflik Paginate vs DataTable

Query pakai `->paginate(10)` (server-side), view pakai `id="table1"` yang kemungkinan diinisialisasi DataTables (client-side). Jika keduanya aktif, DataTables hanya melihat 10 baris dan menganggap itu semua data — search dan sort tidak akurat.

---

### 3.10 🟢 MINOR — Dead Code: Auth Controller Lama

Controller yang tidak dipakai di routes manapun:
- `LoginController`, `RegisterController`, `ConfirmPasswordController`
- `ForgotPasswordController`, `ResetPasswordController`, `VerificationController`

Aman dihapus setelah konfirmasi tidak ada route custom yang memakainya.

---

### 3.11 🟢 MINOR — `updateStatus` Rental Tidak Ada Validasi Status

**Lokasi:** `Admin/RentalController@updateStatus`

```php
$reservation->status = $request->status; // tidak ada validasi nilai yang diterima
```

Admin bisa set status ke nilai apapun termasuk yang tidak valid. Tidak ada pengecekan apakah `$request->status` termasuk `paid`, `ongoing`, atau `returned`.

---

## 4. Daftar File untuk Perbaikan

| File | Perubahan yang Dibutuhkan |
|---|---|
| `app/Http/Controllers/Frontend/PaymentController.php` | Fix status enum + tambah signature verification |
| `app/Http/Controllers/Admin/RentalController.php` | Fix load services setelah paginate + validasi status |
| `app/Http/Controllers/User/ReservationController.php` | Fix filter status + fix show() untuk AJAX |
| `app/Http/Controllers/Admin/ReservationController.php` | Fix scope notifikasi |
| `resources/views/admin/rentals/show.blade.php` | Fix akses `services[0]` tanpa null check |
| `database/migrations/` | Tambah `failed`, `payment_pending` ke enum + kolom `order_expired_at` |
| `app/Http/Controllers/Frontend/ReservationController.php` | Set `order_expired_at` saat store |
| `app/Console/Commands/` | Buat command `ExpirePendingReservations` |
| `app/Console/Kernel.php` | Tambah schedule untuk command expire |

---

## 5. Prioritas Perbaikan

| Prioritas | Item | Estimasi | Risiko Jika Diabaikan |
|---|---|---|---|
| 🔴 P1 | Fix enum status (`failed`, `payment_pending`) | 30 menit | Error 500 saat webhook payment expire |
| 🔴 P1 | Verifikasi signature webhook Midtrans | 30 menit | Manipulasi status pembayaran |
| 🔴 P1 | Fix load services di RentalController setelah paginate | 15 menit | Services tidak pernah ter-load |
| 🟠 P2 | Fix akses `services[0]` di rental show view | 10 menit | Error saat buka detail rental tanpa service |
| 🟠 P2 | Fix filter status di User ReservationController | 15 menit | Semua tab tampilkan data yang sama |
| 🟠 P2 | Fix show() reservation untuk partial view / AJAX | 30 menit | Modal berantakan |
| 🟠 P2 | Tambah `order_expired_at` + command + schedule | 1–2 jam | Reservasi pending tidak pernah expired |
| 🟡 P3 | Fix scope notifikasi by user/role | 15 menit | Notifikasi tercampur antar user |
| 🟡 P3 | Tambah validasi status di updateStatus Rental | 15 menit | Status bisa diset ke nilai invalid |
| 🟡 P3 | Resolve konflik paginate vs DataTable | 30 menit | Pagination dan search tidak akurat |
| 🟢 P4 | Hapus dead code auth controller lama | 30 menit | Dead code, tidak ada dampak fungsional |

---

## 6. Yang Masih Perlu Dicek

- [ ] View `user.reservation.show` — apakah extend layout penuh atau sudah partial?
- [ ] Versi library `midtrans-php` — apakah `new \Midtrans\Notification()` sudah auto-verify signature?
- [ ] Model `Rental` — apakah ada field `status` enum yang terdefinisi di migration?
- [ ] `app/Http/Controllers/User/BookingHistoryController.php` — isinya apa, overlap dengan ReservationController atau tidak?

---

*Dokumen ini dibuat berdasarkan review kode yang tersedia. Poin di seksi 6 memerlukan akses file tambahan untuk konfirmasi.*

---

## 7. Update v3.1 — Temuan Tambahan

### Rental Model
- [x] Relasi sudah lengkap: `user`, `vehicle`, `rental_package`, `services` (via pivot `rental_services`)
- [x] Activity log aktif dengan field: `user_id`, `vehicle_id`, `package_id`, `start_date`, `end_date`, `total_price`, `down_payment_amount`, `status`
- [ ] Tidak ada `SoftDeletes` di model `Rental` (berbeda dengan `Reservation` yang pakai SoftDeletes) — perlu dipertimbangkan konsistensi

### BookingHistoryController
- [x] Tujuan jelas: halaman riwayat — tampilkan `Rental` yang sudah `returned` + `Reservation` yang `canceled`/`failed`
- [x] Tidak overlap dengan `User/ReservationController` — ini khusus history, bukan active reservations

**Bug tambahan:**

#### 3.12 🟠 — BookingHistoryController Query Status `failed` yang Tidak Ada di Enum
```php
$canceledReservations = Reservation::where('user_id', $userId)
    ->whereIn('status', ['canceled', 'failed']) // ❌ 'failed' tidak ada di enum
```
Konsekuensi langsung dari Bug 3.1. Query tidak akan error (MySQL tidak throw untuk nilai yang tidak match), tapi tidak akan pernah return data dengan status `failed` karena status itu tidak bisa tersimpan. Fix ikut Bug 3.1 — setelah enum diperbaiki, update query ini juga.

#### 3.13 🟡 — BookingHistoryController Tidak Pakai Paginate
```php
$completedRentals = Rental::where('user_id', $userId)->...->get();
$canceledReservations = Reservation::where('user_id', $userId)->...->get();
```
Semua data di-load sekaligus. Untuk user dengan banyak transaksi bisa jadi masalah performa. Pertimbangkan `->paginate()` atau setidaknya `->limit()`.

#### Konfirmasi Bug 3.5
View `user.reservation.show` sudah dikonfirmasi extend layout penuh. Modal AJAX akan inject seluruh HTML halaman ke dalam modal. Fix: buat partial view `user.reservation._detail.blade.php` yang tidak extend layout, dan arahkan `show()` ke partial tersebut.