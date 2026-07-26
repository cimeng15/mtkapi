<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name', 'mikrotik_profile', 'rate_limit', 'session_timeout',
        'shared_users', 'data_limit', 'price', 'for_type',
        'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'shared_users' => 'integer',
    ];

    public function hotspotUsers()
    {
        return $this->hasMany(HotspotUser::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
