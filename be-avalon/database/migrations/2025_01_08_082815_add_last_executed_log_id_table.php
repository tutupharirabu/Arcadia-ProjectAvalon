<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('water_pump_alarms', function (Blueprint $table) {
            $table->uuid('last_executed_log_id')->nullable()->after('is_active');
            $table->foreign('last_executed_log_id')->references('water_pump_log_id')->on('water_pump_logs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('water_pump_alarms', function (Blueprint $table) {
            $table->dropColumn('last_executed_log_id');
        });
    }
};
