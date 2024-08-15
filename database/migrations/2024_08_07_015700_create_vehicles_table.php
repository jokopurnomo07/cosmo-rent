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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['car', 'motorcycle']);
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('transmission');
            $table->string('fuel');
            $table->string('registration_number')->unique()->command('License Number');
            $table->integer('capacity')->comment('Number of passengers for cars or engine capacity for motorcycles');
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->string('vehicle_images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
