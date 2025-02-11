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
            $table->string('trx_id')->unique();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('time_pickup')->nullable();
            $table->decimal('total_price', 10, 0)->nullable();
            $table->enum('status', [
                'pending',                // Reservasi baru yang belum diproses
                'canceled',               // Reservasi dibatalkan
                'confirmed',              // Reservasi telah dikonfirmasi
                'expired',                // Reservasi kadaluarsa karena tidak ada tindakan lebih lanjut
                'on_hold',                // Reservasi ditunda sementara waktu
                'rejected',               // Reservasi ditolak oleh admin
                'paid',
            ])->default('pending');
            $table->string('address_pickup')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('reason_canceled')->nullable();
            $table->index('user_id');
            $table->index('vehicle_id');
            $table->timestamps();
            $table->softDeletes();
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
