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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('notifications_id')->primary();
            $table->enum('source', ['admin', 'device']);
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'error', 'alert']);
            $table->uuid('admin_id')->nullable();
            $table->uuid('devices_id')->nullable();
            $table->foreign('admin_id')->references('users_id')->on('users');
            $table->foreign('devices_id')->references('devices_id')->on('devices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
