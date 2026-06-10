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
        Schema::create('rental_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');
            $table->dateTime('extended_until')->comment('New end date for the rental');
            $table->decimal('additional_price', 10, 0)->comment('Cost of extension');
            $table->enum('status', [
                'pending',      // Waiting for admin approval
                'approved',     // Admin approved, awaiting payment
                'rejected',     // Admin rejected
                'paid',         // Payment completed
                'canceled'      // User or admin canceled
            ])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('reason_rejected')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->index('rental_id');
            $table->index('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_extensions');
    }
};
