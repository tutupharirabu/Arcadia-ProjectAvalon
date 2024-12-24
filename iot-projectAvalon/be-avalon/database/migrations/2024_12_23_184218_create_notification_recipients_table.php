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
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->uuid('notification_recipients_id')->primary();
            $table->uuid('notifications_id');
            $table->uuid('users_id')->nullable(); //
            $table->uuid('roles_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreign('notifications_id')->references('notifications_id')->on('notifications');
            $table->foreign('users_id')->references('users_id')->on('users');
            $table->foreign('roles_id')->references('roles_id')->on('roles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
