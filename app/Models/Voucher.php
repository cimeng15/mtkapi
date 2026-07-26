<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'package_id', 'created_by', 'batch', 'username', 'password',
        'price', 'status', 'comment', 'synced', 'mikrotik_id', 'used_at',
    ];

    protected $casts = [
        'synced' => 'boolean',
        'used_at' => 'datetime',
        'price' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
