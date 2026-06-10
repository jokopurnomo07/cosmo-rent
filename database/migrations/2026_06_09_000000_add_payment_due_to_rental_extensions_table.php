<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_extensions', function (Blueprint $table) {
            if (!Schema::hasColumn('rental_extensions', 'payment_due_at')) {
                $table->timestamp('payment_due_at')->nullable()->after('midtrans_order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rental_extensions', function (Blueprint $table) {
            if (Schema::hasColumn('rental_extensions', 'payment_due_at')) {
                $table->dropColumn('payment_due_at');
            }
        });
    }
};
