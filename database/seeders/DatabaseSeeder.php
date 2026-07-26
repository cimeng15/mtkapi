<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Admin utama ----
        User::updateOrCreate(
            ['email' => 'admin@sekolah.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@sekolah.id'],
            [
                'name' => 'Operator TU',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'is_active' => true,
            ]
        );

        // ---- Paket contoh ----
        $packages = [
            [
                'name' => 'Guru Unlimited',
                'mikrotik_profile' => 'guru-unlimited',
                'rate_limit' => '4M/2M',
                'session_timeout' => null,
                'shared_users' => 2,
                'price' => 0,
                'for_type' => 'guru',
                'description' => 'Akses penuh tanpa batas durasi untuk guru & staff.',
            ],
            [
                'name' => 'Siswa 3 Jam',
                'mikrotik_profile' => 'siswa-3jam',
                'rate_limit' => '2M/1M',
                'session_timeout' => '3h',
                'shared_users' => 1,
                'price' => 2000,
                'for_type' => 'siswa',
                'description' => 'Paket belajar 3 jam untuk siswa.',
            ],
            [
                'name' => 'Voucher Harian',
                'mikrotik_profile' => 'harian',
                'rate_limit' => '3M/1M',
                'session_timeout' => '1d',
                'shared_users' => 1,
                'price' => 5000,
                'for_type' => 'umum',
                'description' => 'Voucher umum berlaku 1 hari.',
            ],
        ];

        foreach ($packages as $p) {
            Package::updateOrCreate(['name' => $p['name']], $p);
        }
    }
}
