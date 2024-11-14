<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;

class Parameter
{
    protected $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    // Menyimpan parameter ke Redis
    public function storeParameter($devices_id, $parameter)
    {
        $key = "device:{$devices_id}:parameters";
        $this->redis->rpush($key, [json_encode($parameter)]);
    }

    // Mengambil semua parameter dari Redis
    public function getParameters($devices_id)
    {
        $key = "device:{$devices_id}:parameters";
        return array_map('json_decode', $this->redis->lrange($key, 0, -1));
    }

    // Menghapus semua parameter dari Redis (setelah dipindahkan ke MySQL)
    public function clearParameters($devices_id)
    {
        $key = "device:{$devices_id}:parameters";
        $this->redis->del($key);
    }
}
