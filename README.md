# 🚗 Cosmo-Rent — Vehicle Rental Management System

A web-based vehicle rental management system built with Laravel, featuring reservation management, online payment via Midtrans, and role-based access for admin and users.

---

## 📋 Table of Contents

- [Requirements](#requirements)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Local Installation](#local-installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Default Accounts](#default-accounts)
- [Payment Testing (Midtrans Sandbox)](#payment-testing-midtrans-sandbox)
- [Deployment to Railway](#deployment-to-railway)
- [Project Structure](#project-structure)

---

## Requirements

Before you begin, make sure you have the following installed:

- PHP >= 8.3
- Composer
- Node.js >= 20.x & NPM
- PostgreSQL >= 14
- Git
- [Laragon](https://laragon.org/) (recommended for Windows) or Laravel Herd / XAMPP

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 10 |
| Frontend | Blade, Tailwind CSS, Vite |
| Database | PostgreSQL |
| Payment | Midtrans Snap |
| Authentication | Laravel Breeze |
| Mail | SMTP / Mailpit (local) |

---

## Features

### Admin
- Dashboard with reservation & rental overview
- Manage vehicles (CRUD)
- Manage reservations — confirm, reject, cancel
- Manage rentals — update status
- Manage users
- Export rental reports

### User
- Browse available vehicles
- Create reservations
- Online payment via Midtrans
- View reservation & rental history
- Cancel reservations

---

## Local Installation

### 1. Clone the repository

```bash
git clone https://github.com/jokopurnomo07/cosmo-rent.git
cd cosmo-rent
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Copy environment file

```bash
cp .env.example .env
```

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Configure your `.env` file

Open `.env` and update the following values:

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

MIDTRANS_SERVER_KEY=SB-Mid-server-your-sandbox-key
MIDTRANS_IS_PRODUCTION=false

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=hello@cosmorent.com
MAIL_FROM_NAME="cosmo-rent"
```

> Get your Midtrans sandbox key from [sandbox.midtrans.com](https://sandbox.midtrans.com) → Settings → Access Keys

---

## Database Setup

### 1. Create the database

```bash
# Using psql
psql -U postgres
CREATE DATABASE "cosmo-rent";
\q
```

### 2. Run migrations

```bash
php artisan migrate
```

### 3. Run seeders (optional — creates default admin & sample data)

```bash
php artisan db:seed
```

---

## Running the Application

### Start all services at once (recommended)

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Vite asset compiler
npm run dev
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

### If using Laragon with custom domain

Laragon auto-serves at `http://cosmorent.test`. Just run:

```bash
npm run dev
```

---

## Default Accounts

After running seeders:

| Role | Email | Password |
|---|---|---|
| Admin | admin@cosmorent.com | password |
| User | user@cosmorent.com | password |

> If no seeder is available, register a new account and manually update the `role` column in the `users` table to `admin`.

---

## Payment Testing (Midtrans Sandbox)

This app uses **Midtrans Sandbox** — no real money is involved.

### Test Credit Card

| Field | Value |
|---|---|
| Card Number | `4811 1111 1111 1114` (success) |
| Card Number | `4911 1111 1111 1113` (failure) |
| CVV | `123` |
| Expiry | Any future date |
| OTP / 3DS | `112233` |

### Webhook (local development)

Midtrans cannot hit `localhost` directly. Use **ngrok** to expose your local server:

```bash
# Install ngrok from https://ngrok.com/download
# Then expose your local server
ngrok http 8000

# Or if using Laragon custom domain
ngrok http cosmorent.test --host-header=cosmorent.test
```

Then set the notification URL in [Midtrans sandbox dashboard](https://sandbox.midtrans.com):

```
Settings → Configuration → Payment Notification URL
https://your-ngrok-url.ngrok.io/midtrans/notification
```

---

## Deployment to Railway

### Prerequisites
- [Railway account](https://railway.app)
- GitHub repository connected to Railway

### Step 1 — Add PostgreSQL service

Railway dashboard → **New** → **Database** → **PostgreSQL**

### Step 2 — Deploy Laravel service

Railway dashboard → **New** → **GitHub Repo** → select your repo

### Step 3 — Set Environment Variables

Go to your Laravel service → **Variables** → add:

```
APP_NAME=cosmo-rent
APP_ENV=production
APP_DEBUG=false
APP_KEY=           ← php artisan key:generate --show
APP_URL=           ← your Railway domain

DB_CONNECTION=pgsql
DB_HOST=           ← from Railway PostgreSQL → Connect → Laravel
DB_PORT=           ← from Railway PostgreSQL → Connect → Laravel
DB_DATABASE=       ← from Railway PostgreSQL → Connect → Laravel
DB_USERNAME=       ← from Railway PostgreSQL → Connect → Laravel
DB_PASSWORD=       ← from Railway PostgreSQL → Connect → Laravel

MIDTRANS_SERVER_KEY=SB-Mid-server-your-sandbox-key
MIDTRANS_IS_PRODUCTION=false

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@cosmorent.com
MAIL_FROM_NAME=cosmo-rent
```

> To get DB credentials: Railway → PostgreSQL service → **Connect** tab → **Laravel** section

### Step 4 — Set Midtrans notification URL

[sandbox.midtrans.com](https://sandbox.midtrans.com) → Settings → Configuration:

```
Payment Notification URL: https://your-app.up.railway.app/midtrans/notification
Finish Redirect URL: https://your-app.up.railway.app/payments/finish
```

### Step 5 — Deploy

Railway will auto-deploy on every push to your main branch. Migrations run automatically on startup.

---

## Project Structure

```
cosmo-rent/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin controllers
│   │   │   ├── Frontend/       # Public-facing controllers
│   │   │   └── User/           # Authenticated user controllers
│   │   └── Middleware/
│   ├── Mail/                   # Mailable classes
│   ├── Models/                 # Eloquent models
│   └── Providers/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── admin/              # Admin panel views
│   │   ├── frontend/           # Public views
│   │   └── user/               # User dashboard views
│   └── js/
├── routes/
│   └── web.php
├── nixpacks.toml               # Railway Nixpacks build config
├── railway.toml                # Railway deployment config
└── .env.example
```

---

## Security Notes

- Never commit `.env` to git — it is listed in `.gitignore`
- Always rotate `APP_KEY` before going to production
- Set `APP_DEBUG=false` in production
- Use a strong `DB_PASSWORD` in production
- Switch `MIDTRANS_IS_PRODUCTION=true` and use production server key only when the app is ready for real transactions

---

## License

This project is developed as a final project (Skripsi) and is intended for academic purposes.