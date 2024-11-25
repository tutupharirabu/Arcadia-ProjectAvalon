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
        Schema::create('historical_data', function (Blueprint $table) {
            $table->uuid('history_id')->primary();
            $table->json('parameters');
            $table->timestamp('waktu_diambil');
            $table->uuid('devices_id');
            $table->foreign('devices_id')->references('devices_id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_data');
    }
};
