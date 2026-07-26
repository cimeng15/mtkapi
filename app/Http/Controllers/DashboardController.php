<?php

namespace App\Http\Controllers;

use App\Models\HotspotUser;
use App\Models\Member;
use App\Models\Package;
use App\Models\Voucher;
use App\Services\MikrotikService;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'members' => Member::count(),
            'guru' => Member::where('type', 'guru')->count(),
            'siswa' => Member::where('type', 'siswa')->count(),
            'hotspot_users' => HotspotUser::count(),
            'packages' => Package::count(),
            'vouchers' => Voucher::count(),
            'vouchers_unused' => Voucher::where('status', 'unused')->count(),
        ];

        $router = null;
        $resource = null;
        $activeCount = 0;
        $activeUsers = [];
        $error = null;

        $mt = new MikrotikService();
        if ($mt->hasSetting()) {
            try {
                $resource = $mt->resource();
                $activeUsers = $mt->activeUsers();
                $activeCount = count($activeUsers);
                $router = $mt->setting();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'Belum ada konfigurasi router MikroTik. Silakan atur di menu Pengaturan Router.';
        }

        return view('dashboard', compact(
            'stats', 'resource', 'activeCount', 'activeUsers', 'router', 'error'
        ));
    }
}
