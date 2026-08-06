<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Node.js (MQTT Bridge) URLs
    |--------------------------------------------------------------------------
    |
    | URL servis node_mqtt_server yang menjembatani MQTT broker dengan Laravel.
    | Dipanggil lewat config() agar tidak pecah setelah `php artisan config:cache`.
    |
    */

    'url_1' => env('NODE_API_URL_1'),

    'url_2' => env('NODE_API_URL_2'),

    /*
    |--------------------------------------------------------------------------
    | Shared Secret (Laravel ↔ Node.js)
    |--------------------------------------------------------------------------
    |
    | Nilai yang SAMA harus di-set di .env Laravel DAN .env node_mqtt_server.
    | Dikirim sebagai header `x-shared-secret` oleh Laravel dan diverifikasi
    | fail-closed oleh middleware requireSharedSecret di node_mqtt_server.
    |
    */

    'shared_secret' => env('SHARED_SECRET'),

];
