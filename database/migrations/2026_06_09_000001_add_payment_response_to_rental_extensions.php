<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rental_extensions', 'payment_response')) {
            Schema::table('rental_extensions', function (Blueprint $table) {
                $table->text('payment_response')->nullable()->after('midtrans_order_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('rental_extensions', function (Blueprint $table) {
            $table->dropColumn('payment_response');
        });
    }
};
