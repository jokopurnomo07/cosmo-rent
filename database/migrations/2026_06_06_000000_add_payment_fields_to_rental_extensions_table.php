<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->string('payment_url')->nullable()->after('additional_price');
            $table->string('midtrans_order_id')->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'midtrans_order_id']);
        });
    }
};
