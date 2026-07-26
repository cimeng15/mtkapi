<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikSetting extends Model
{
    protected $fillable = [
        'name', 'host', 'port', 'username', 'password',
        'use_ssl', 'is_active', 'dns_name', 'last_connected_at',
    ];

    protected $casts = [
        'use_ssl' => 'boolean',
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    /**
     * Ambil konfigurasi router yang sedang aktif.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
