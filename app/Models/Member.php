<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_id', 'name', 'type', 'package_id', 'class',
        'department', 'phone', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function hotspotUser()
    {
        return $this->hasOne(HotspotUser::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
