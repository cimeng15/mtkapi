<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotspotUser extends Model
{
    protected $fillable = [
        'member_id', 'package_id', 'username', 'password',
        'comment', 'status', 'synced', 'synced_at', 'mikrotik_id',
    ];

    protected $casts = [
        'synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
