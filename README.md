# 🚗 Cosmo-Rent — Vehicle Rental Management System

Sistem manajemen rental kendaraan berbasis web, dibangun dengan Laravel 10. Mendukung alur reservasi lengkap, pembayaran online via Midtrans Snap, perpanjangan rental, pelacakan lokasi kendaraan real-time, notifikasi in-app, dan role-based access untuk Admin dan User.

> 📌 Proyek ini dikembangkan sebagai Tugas Akhir (Skripsi) dan dideploy di [Railway](https://railway.app).

---

## 📋 Daftar Isi

- [Tech Stack](#tech-stack)
- [Fitur](#fitur)
- [Alur Sistem](#alur-sistem)
- [Skema Database](#skema-database)
- [Instalasi Lokal](#instalasi-lokal)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Setup Database](#setup-database)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Scheduled Tasks](#scheduled-tasks)
- [Akun Default](#akun-default)
- [Testing Pembayaran Midtrans](#testing-pembayaran-midtrans)
- [Deployment ke Railway](#deployment-ke-railway)
- [Struktur Proyek](#struktur-proyek)
- [Catatan Keamanan](#catatan-keamanan)

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 10, PHP 8.3 |
| Frontend | Blade, Tailwind CSS, Vite |
| Database | PostgreSQL 14+ |
| Payment | Midtrans Snap (`midtrans/midtrans-php ^2.5`) |
| Authentication | Laravel Breeze + Spatie Permission (`^6.13`) |
| Realtime | Pusher (`pusher/pusher-php-server ^7.2`) |
| Export | Maatwebsite Excel (`^3.1`) |
| Activity Log | Spatie Laravel Activitylog (`^4.8`) |
| Media | Spatie Media Library (`^10.15`) |
| DataTable | Yajra Laravel Datatables (`10.0`) |
| HTTP Client | Guzzle (`^7.2`) |
| Mail | SMTP / Mailpit (lokal) |
| Deployment | Railway + Nixpacks |

---

## Fitur

### Admin
- Dashboard overview reservasi & rental aktif
- CRUD kendaraan (tipe, merek, transmisi, bahan bakar, kapasitas, gambar, stok)
- Manajemen reservasi — konfirmasi, tolak, batalkan, generate Midtrans payment URL
- Manajemen rental — update status (paid → ongoing → returned)
- Approve/reject perpanjangan rental
- Tracking lokasi kendaraan real-time (via GPS device atau webhook)
- Export laporan rental ke Excel dengan filter rentang tanggal mulai rental
- Manajemen user

### User
- Browse kendaraan yang tersedia
- Buat reservasi dengan pilihan paket durasi & layanan tambahan (khusus tipe mobil)
- Pembayaran online via Midtrans Snap
- Ajukan perpanjangan rental (maks. 1x per rental, status ongoing/paid)
- Bayar biaya perpanjangan via Midtrans
- Lihat histori reservasi & rental
- Batalkan reservasi sendiri
- Notifikasi in-app untuk setiap perubahan status

---

## Alur Sistem

### Reservasi → Rental

```
[User] Buat Reservasi
    → status: pending
    → [Admin] Konfirmasi
    → status: confirmed + Midtrans payment URL dibuat + email dikirim ke user
    → [User] Klik link & bayar
    → Webhook Midtrans settlement
    → status: paid + Rental dibuat otomatis + kendaraan dikunci (status: rented)
    → [Admin] Update Rental: paid → ongoing → returned
```

### Perpanjangan Rental

```
[User] Ajukan Perpanjangan (saat rental ongoing/paid)
    → Extension status: pending
    → [Admin] Approve
    → status: approved + Midtrans payment URL dibuat + email dikirim ke user
    → [User] Bayar perpanjangan
    → Webhook settlement → status: paid + end_date rental diperbarui
```

### Status Reservasi

| Status | Keterangan | Diset Oleh |
|---|---|---|
| `pending` | Menunggu konfirmasi admin | Default saat dibuat |
| `confirmed` | Dikonfirmasi, menunggu pembayaran | Admin |
| `paid` | Pembayaran berhasil, Rental sudah dibuat | Webhook Midtrans |
| `expired` | Pembayaran gagal/expired/dibatalkan | Webhook Midtrans |
| `canceled` | Dibatalkan oleh user atau admin | User / Admin |
| `rejected` | Ditolak admin | Admin |

### Status Rental

| Status | Keterangan |
|---|---|
| `paid` | Pembayaran diterima, kendaraan belum diserahkan |
| `ongoing` | Kendaraan sedang digunakan |
| `returned` | Kendaraan sudah dikembalikan |
| `completed` | Rental selesai dikonfirmasi |
| `awaiting_confirmation` | Menunggu konfirmasi |
| `payment_failed` | Pembayaran gagal |

### Status Perpanjangan

| Status | Keterangan |
|---|---|
| `pending` | Menunggu persetujuan admin |
| `approved` | Disetujui, menunggu pembayaran |
| `paid` | Pembayaran selesai, end_date rental diperbarui |
| `rejected` | Ditolak admin |
| `canceled` | Dibatalkan atau pembayaran expired |

---

## Skema Database

| Tabel | Keterangan |
|---|---|
| `users` | Admin & user, role via Spatie Permission |
| `vehicles` | Data kendaraan (car/motorcycle), stok, status |
| `vehicle_features` | Fitur kendaraan (pivot) |
| `vehicle_prices` | Harga per paket per kendaraan |
| `rental_packages` | Paket durasi sewa (dalam jam) |
| `services` | Layanan tambahan (khusus mobil) |
| `reservations` | Reservasi sebelum jadi rental, support SoftDeletes |
| `reservation_services` | Layanan yang dipilih saat reservasi (pivot) |
| `rentals` | Rental aktif, dibuat otomatis dari webhook Midtrans |
| `rental_services` | Layanan yang di-copy dari reservasi ke rental (pivot) |
| `rental_extensions` | Pengajuan perpanjangan rental beserta data pembayaran |
| `rental_location_logs` | Log GPS kendaraan (lat, lng, address, speed km/h) |
| `notifications` | Notifikasi in-app per user |
| `activity_log` | Log aktivitas sistem via Spatie Activitylog |

---

## Instalasi Lokal

### Requirements

- PHP >= 8.3 dengan ekstensi: `pdo_pgsql`, `mbstring`, `xml`, `gd`, `bcmath`, `curl`, `fileinfo`, `openssl`
- Composer
- Node.js >= 20.x & NPM
- PostgreSQL >= 14
- Git
- [Laragon](https://laragon.org/) (Windows) atau Laravel Herd / XAMPP

### Langkah-langkah

```bash
# 1. Clone repo
git clone https://github.com/jokopurnomo07/cosmo-rent.git
cd cosmo-rent

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node
npm install

# 4. Copy file environment
cp .env.example .env

# 5. Generate app key
php artisan key:generate
```

---

## Konfigurasi Environment

Buka `.env` dan sesuaikan nilai berikut:

```dotenv
APP_NAME=cosmo-rent
APP_ENV=local
APP_DEBUG=true
APP_URL=http://cosmorent.test

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cosmo-rent
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Midtrans
MIDTRANS_SERVER_KEY=SB-Mid-server-your-sandbox-key
MIDTRANS_CLIENT_KEY=SB-Mid-client-your-sandbox-key
MIDTRANS_IS_PRODUCTION=false

# Pusher (realtime tracking & notifikasi)
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Mail
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=hello@cosmorent.com
MAIL_FROM_NAME="cosmo-rent"

# Tracking device authentication
TRACKING_SECRET=your-secret-token
```

> Midtrans sandbox key: [sandbox.midtrans.com](https://sandbox.midtrans.com) → Settings → Access Keys

---

## Setup Database

```bash
# Buat database
psql -U postgres -c 'CREATE DATABASE "cosmo-rent";'

# Jalankan migrasi
php artisan migrate

# (Opsional) Jalankan seeder — membuat akun, kendaraan, paket, & sample data
php artisan db:seed
```

Seeder tersedia: `UserSeeder`, `RoleSeeder`, `VehicleSeeder`, `RentalPackageSeeder`, `VehiclePriceSeeder`, `ServiceSeeder`, `FeatureSeeder`, `RentalSeeder`, `ReservationSeeder`.

---

## Menjalankan Aplikasi

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Vite asset compiler
npm run dev

# Terminal 3 — Queue worker (untuk email & event)
php artisan queue:work

# Terminal 4 — Scheduler
php artisan schedule:work
```

Buka [http://localhost:8000](http://localhost:8000) di browser.

> Jika menggunakan Laragon: akses di `http://cosmorent.test`, cukup jalankan `npm run dev`.

---

## Scheduled Tasks

| Command | Jadwal | Fungsi |
|---|---|---|
| `notify:pickup` | Setiap hari | Kirim email ke user yang kendaraannya siap diambil hari itu |
| `extensions:cancel-expired` | Setiap jam | Batalkan perpanjangan yang melewati batas waktu pembayaran (`payment_due_at`) |

Untuk production, pastikan cron berjalan:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Akun Default

Setelah menjalankan seeder:

| Role | Email | Password |
|---|---|---|
| Admin | admin@cosmorent.com | password |
| User | user@cosmorent.com | password |

> Jika seeder belum tersedia: daftar akun baru, lalu ubah kolom `role` di tabel `users` menjadi `admin` secara manual lewat database.

---

## Testing Pembayaran Midtrans

Aplikasi ini menggunakan **Midtrans Sandbox** — tidak ada uang sungguhan yang diproses.

### Kartu Kredit Test

| Field | Value |
|---|---|
| Nomor Kartu (sukses) | `4811 1111 1111 1114` |
| Nomor Kartu (gagal) | `4911 1111 1111 1113` |
| CVV | `123` |
| Expiry | Tanggal apa saja di masa depan |
| OTP / 3DS | `112233` |

### Webhook (Development Lokal)

Midtrans tidak bisa hit `localhost` secara langsung. Gunakan **ngrok**:

```bash
ngrok http 8000

# Jika menggunakan Laragon custom domain
ngrok http cosmorent.test --host-header=cosmorent.test
```

Set URL di [sandbox.midtrans.com](https://sandbox.midtrans.com) → Settings → Configuration:

```
Payment Notification URL : https://your-ngrok-url.ngrok.io/midtrans/notification
Finish Redirect URL      : https://your-ngrok-url.ngrok.io/payments/finish
```

> Route `/midtrans/notification` sudah dikecualikan dari CSRF verification.

---

## Deployment ke Railway

### Prasyarat
- Akun [Railway](https://railway.app)
- Repo GitHub terhubung ke Railway

### Langkah-langkah

**1. Tambahkan PostgreSQL service**

Railway dashboard → New → Database → PostgreSQL

**2. Deploy Laravel service**

Railway dashboard → New → GitHub Repo → pilih repo ini

**3. Set Environment Variables**

Masuk ke Laravel service → Variables → tambahkan semua nilai dari `.env`, disesuaikan untuk production:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=           ← php artisan key:generate --show
APP_URL=           ← domain Railway kamu

DB_CONNECTION=pgsql
DB_HOST=           ← Railway PostgreSQL → Connect → Laravel
DB_PORT=           ← Railway PostgreSQL → Connect → Laravel
DB_DATABASE=       ← Railway PostgreSQL → Connect → Laravel
DB_USERNAME=       ← Railway PostgreSQL → Connect → Laravel
DB_PASSWORD=       ← Railway PostgreSQL → Connect → Laravel

MIDTRANS_SERVER_KEY=SB-Mid-server-your-key
MIDTRANS_CLIENT_KEY=SB-Mid-client-your-key
MIDTRANS_IS_PRODUCTION=false

MAIL_MAILER=log    ← ganti ke SMTP provider jika butuh email production
TRACKING_SECRET=your-secret-token
```

**4. Set Midtrans notification URL**

```
Payment Notification URL : https://your-app.up.railway.app/midtrans/notification
Finish Redirect URL      : https://your-app.up.railway.app/payments/finish
```

**5. Deploy**

Railway auto-deploy setiap push ke branch `main`. Startup command sudah dikonfigurasi via `nixpacks.toml`:

```
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan migrate --force
php artisan serve --host=0.0.0.0 --port=$PORT
```

---

## Struktur Proyek

```
cosmo-rent/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── CancelExpiredExtensions.php   # Batalkan perpanjangan yang melewati payment_due_at
│   │       └── NotifyUsersForPickup.php       # Email harian notifikasi siap pickup
│   ├── Events/
│   │   ├── ReservationCreated.php
│   │   ├── ExtensionRequested.php
│   │   ├── ExtensionApproved.php
│   │   └── ExtensionPaid.php
│   ├── Exports/
│   │   └── RentalsExport.php                  # Export Excel laporan rental
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                         # Dashboard, Vehicle, Reservation, Rental, Extension, Tracking
│   │   │   ├── Frontend/                      # Home, Vehicle, Reservation, Payment, RentalExtension
│   │   │   └── User/                          # Reservation, Rental, BookingHistory
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Mail/                                  # ExtensionApproved, ReservationConfirmation, RejectionNotification, dll
│   └── Models/
│       ├── Rental.php
│       ├── RentalExtension.php
│       ├── RentalLocationLog.php
│       ├── RentalPackage.php
│       ├── Reservation.php
│       ├── Service.php
│       ├── User.php
│       ├── Vehicle.php
│       └── Notification.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── admin/      # Panel admin
│   │   ├── frontend/   # Halaman publik
│   │   └── user/       # Dashboard user
│   └── js/
├── routes/
│   └── web.php
├── nixpacks.toml        # Build & start config Railway
└── .env.example
```

---

## Catatan Keamanan

- Jangan commit file `.env` — sudah ada di `.gitignore`
- Selalu rotate `APP_KEY` sebelum production
- Set `APP_DEBUG=false` di production
- Gunakan password DB yang kuat di production
- Ganti `MIDTRANS_IS_PRODUCTION=true` dan gunakan production server key hanya saat siap transaksi nyata
- Endpoint tracking publik (`POST /tracking/{rental_id}/add-location`) diproteksi header `X-TRACKING-TOKEN` yang dicocokkan dengan `TRACKING_SECRET` di env
- Route `/midtrans/notification` dikecualikan dari CSRF — pastikan signature verification Midtrans aktif di production

---

## Lisensi

Proyek ini dikembangkan sebagai Tugas Akhir (Skripsi) untuk tujuan akademis.