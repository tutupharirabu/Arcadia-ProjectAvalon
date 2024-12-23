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
        Schema::create('water_pump_alarm', function (Blueprint $table) {
            $table->uuid('water_pump_alarm_id')->primary();
            $table->uuid('devices_id');
            $table->foreign('devices_id')->references('devices_id')->on('devices');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_pump_alarm');
    }
};
