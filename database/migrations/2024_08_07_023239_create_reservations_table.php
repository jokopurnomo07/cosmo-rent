<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Menangani pengguna yang tidak login
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('rental_package_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('time_pickup')->nullable();
            $table->decimal('total_price', 10, 0)->nullable();
            $table->decimal('down_payment_amount', 10, 0)->nullable();
            $table->enum('down_payment_status', ['not_required', 'pending', 'paid', 'failed'])->default('not_required');
            $table->enum('status', [
                'pending',                // Reservasi baru yang belum diproses
                'awaiting_down_payment',  // Menunggu pembayaran uang muka
                'partially_paid',         // Pembayaran sebagian telah diterima
                'canceled',               // Reservasi dibatalkan
                'confirmed',              // Reservasi telah dikonfirmasi
                'awaiting_confirmation',  // Menunggu konfirmasi dari admin atau sistem
                'payment_failed',         // Pembayaran gagal dilakukan
                'expired',                // Reservasi kadaluarsa karena tidak ada tindakan lebih lanjut
                'on_hold',                // Reservasi ditunda sementara waktu
                'rejected',               // Reservasi ditolak oleh admin
                'in_progress',            // Reservasi sedang dalam proses tetapi belum "ongoing"
            ])->default('pending');
            $table->string('nama_guest')->nullable();
            $table->string('email_guest')->nullable();
            $table->string('no_hp_guest')->nullable();
            $table->string('address_pickup')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->index('user_id');
            $table->index('vehicle_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
