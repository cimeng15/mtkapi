<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MikrotikSetting;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class MikrotikSettingController extends Controller
{
    public function edit()
    {
        $setting = MikrotikSetting::active() ?? new MikrotikSetting([
            'host' => config('mikrotik.host', env('MIKROTIK_HOST', '192.168.88.1')),
            'port' => env('MIKROTIK_PORT', 8728),
            'username' => env('MIKROTIK_USER', 'admin'),
        ]);

        return view('settings.mikrotik', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:100',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:100',
            'password' => 'nullable|string|max:200',
            'use_ssl' => 'nullable|boolean',
            'dns_name' => 'nullable|string|max:150',
        ]);

        $setting = MikrotikSetting::active() ?? new MikrotikSetting();

        $setting->fill([
            'name' => $data['name'],
            'host' => $data['host'],
            'port' => $data['port'],
            'username' => $data['username'],
            'use_ssl' => $request->boolean('use_ssl'),
            'dns_name' => $data['dns_name'] ?? null,
            'is_active' => true,
        ]);

        // Password hanya di-set jika diisi (kosong = pertahankan yang lama)
        if (! empty($data['password'])) {
            $setting->password = Crypt::encryptString($data['password']);
        }

        $setting->save();

        ActivityLog::record('setting.update', 'Memperbarui konfigurasi router: '.$setting->host);

        return redirect()->route('settings.mikrotik.edit')
            ->with('success', 'Konfigurasi router berhasil disimpan.');
    }

    public function test()
    {
        $mt = new MikrotikService();
        try {
            $info = $mt->testConnection();
            return back()->with('success', 'Koneksi berhasil! Router: '.$info['identity']
                .' — RouterOS '.($info['resource']['version'] ?? '?')
                .', uptime '.($info['resource']['uptime'] ?? '?'));
        } catch (Throwable $e) {
            return back()->with('error', 'Koneksi gagal: '.$e->getMessage());
        }
    }
}
