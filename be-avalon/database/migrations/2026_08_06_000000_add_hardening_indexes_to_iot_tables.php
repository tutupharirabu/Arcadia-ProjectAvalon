<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hardening database (Wave 2) — additive-only.
     *
     * 1. Index `is_active` di water_pump_alarms — tabel ini di-scan tiap menit oleh
     *    scheduled command `check:alarms` (bootstrap/app.php), tanpa index query-nya
     *    akan full-scan seiring data bertambah.
     * 2. Composite index (devices_id, created_at) di historical_data — semua query
     *    riwayat sensor per device memfilter devices_id lalu mengurutkan waktu.
     * 3. Timestamps NULLABLE di otp_codes — baris lama dibiarkan kosong; model
     *    OTP_codes diaktifkan `$timestamps` (lihat app/Models/OTP_codes.php).
     * 4. onDelete('cascade') konsisten di:
     *    - water_pump_logs.devices_id
     *    - water_pump_alarms.devices_id
     *    - notification_recipients.notifications_id
     *    - notification_recipients.users_id
     *    Sebelumnya RESTRICT tanpa try/catch di level aplikasi → delete device/user
     *    yang punya dependensi melempar 500 tak tertangani. Tabel `users` TIDAK disentuh.
     * 5. Unique (devices_id, start_time, end_time) di water_pump_alarms — menopang key
     *    dedup `updateOrCreate` di WaterPumpAlarmController::updateOrCreate sehingga
     *    race condition tidak bisa lagi membuat alarm duplikat.
     *
     * CATATAN RISIKO (MySQL prod) untuk bagian dedup unique:
     * - Dedup memakai window function ROW_NUMBER() → butuh MySQL 8.0+ / SQLite 3.25+.
     *   DB produksi Railway memakai MySQL 8.x sehingga aman.
     * - Jika ternyata dijalankan di MySQL 5.7, statement dedup akan melempar error
     *   (migration berhenti SEBELUM menambah unique) — bukan menghapus data sembarangan.
     *   Solusi: jalankan dedup manual di DB, lalu komentari blok DB::statement ini.
     * - "Keep row tertua": ORDER BY created_at ASC — MySQL & SQLite mengurutkan NULL
     *   paling awal dengan ASC, jadi baris legacy tanpa created_at dianggap paling tua.
     */
    public function up(): void
    {
        // 1. Index untuk scan cron per-menit
        if (Schema::hasTable('water_pump_alarms')) {
            Schema::table('water_pump_alarms', function (Blueprint $table) {
                $table->index('is_active', 'idx_water_pump_alarms_is_active');
            });
        }

        // 2. Composite index riwayat sensor per device
        if (Schema::hasTable('historical_data')) {
            Schema::table('historical_data', function (Blueprint $table) {
                $table->index(['devices_id', 'created_at'], 'idx_historical_data_devices_created');
            });
        }

        // 3. Timestamps otp_codes (nullable — data lama tetap kosong)
        if (Schema::hasTable('otp_codes')) {
            Schema::table('otp_codes', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // 4a. FK cascade — water_pump_logs.devices_id
        if (Schema::hasTable('water_pump_logs')) {
            Schema::table('water_pump_logs', function (Blueprint $table) {
                $table->dropForeign(['devices_id']);
            });
            Schema::table('water_pump_logs', function (Blueprint $table) {
                $table->foreign('devices_id')->references('devices_id')->on('devices')->onDelete('cascade');
            });
        }

        // 4b. FK cascade — water_pump_alarms.devices_id
        if (Schema::hasTable('water_pump_alarms')) {
            Schema::table('water_pump_alarms', function (Blueprint $table) {
                $table->dropForeign(['devices_id']);
            });
            Schema::table('water_pump_alarms', function (Blueprint $table) {
                $table->foreign('devices_id')->references('devices_id')->on('devices')->onDelete('cascade');
            });
        }

        // 4c. FK cascade — notification_recipients.notifications_id & users_id
        if (Schema::hasTable('notification_recipients')) {
            Schema::table('notification_recipients', function (Blueprint $table) {
                $table->dropForeign(['notifications_id']);
                $table->dropForeign(['users_id']);
            });
            Schema::table('notification_recipients', function (Blueprint $table) {
                $table->foreign('notifications_id')->references('notifications_id')->on('notifications')->onDelete('cascade');
                $table->foreign('users_id')->references('users_id')->on('users')->onDelete('cascade');
            });
        }

        // 5. Dedup duplikat alarm (keep row tertua) lalu tambah unique constraint
        if (Schema::hasTable('water_pump_alarms')) {
            // Hapus duplikat (devices_id, start_time, end_time), pertahankan row tertua.
            // Derived table ganda dibutuhkan agar MySQL tidak memunculkan error
            // "You can't specify target table for update in FROM clause" (error 1093).
            DB::statement(<<<'SQL'
                DELETE FROM water_pump_alarms
                WHERE water_pump_alarm_id NOT IN (
                    SELECT water_pump_alarm_id FROM (
                        SELECT water_pump_alarm_id,
                               ROW_NUMBER() OVER (
                                   PARTITION BY devices_id, start_time, end_time
                                   ORDER BY created_at ASC, water_pump_alarm_id ASC
                               ) AS rn
                        FROM water_pump_alarms
                    ) ranked
                    WHERE rn = 1
                )
            SQL);

            Schema::table('water_pump_alarms', function (Blueprint $table) {
                $table->unique(['devices_id', 'start_time', 'end_time'], 'uq_water_pump_alarms_device_times');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('water_pump_alarms')) {
            Schema::table('water_pump_alarms', function (Blueprint $table) {
                $table->dropUnique('uq_water_pump_alarms_device_times');
                $table->dropIndex('idx_water_pump_alarms_is_active');
            });
        }

        if (Schema::hasTable('historical_data')) {
            Schema::table('historical_data', function (Blueprint $table) {
                $table->dropIndex('idx_historical_data_devices_created');
            });
        }

        if (Schema::hasTable('otp_codes')) {
            Schema::table('otp_codes', function (Blueprint $table) {
                $table->dropColumn(['created_at', 'updated_at']);
            });
        }

        // FK yang diubah ke cascade tidak dikembalikan ke RESTRICT di sini:
        // rollback FK berisiko di SQLite (rebuild tabel) dan tidak wajib secara
        // fungsional. Migration ini bersifat additive.
    }
};
