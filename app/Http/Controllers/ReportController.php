<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HotspotUser;
use App\Models\Member;
use App\Models\Voucher;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Throwable;

class ReportController extends Controller
{
    public function index()
    {
        $summary = [
            'total_members' => Member::count(),
            'total_hotspot' => HotspotUser::count(),
            'hotspot_active' => HotspotUser::where('status', 'active')->count(),
            'voucher_total' => Voucher::count(),
            'voucher_used' => Voucher::where('status', 'used')->count(),
            'voucher_unused' => Voucher::where('status', 'unused')->count(),
            'pendapatan_potensial' => Voucher::where('status', 'used')->sum('price'),
        ];

        // Voucher per batch
        $batches = Voucher::selectRaw('batch, count(*) as total,
                sum(case when status = "used" then 1 else 0 end) as used,
                sum(price) as nilai')
            ->whereNotNull('batch')
            ->groupBy('batch')
            ->orderByDesc('batch')
            ->limit(20)
            ->get();

        // Pemakaian dari router (bytes per user)
        $usage = [];
        $error = null;
        $mt = new MikrotikService();
        if ($mt->hasSetting()) {
            try {
                $rows = $mt->listHotspotUsers();
                foreach ($rows as $r) {
                    $usage[] = [
                        'name' => $r['name'] ?? '-',
                        'profile' => $r['profile'] ?? '-',
                        'uptime' => $r['uptime'] ?? '-',
                        'bytes_in' => (int) ($r['bytes-in'] ?? 0),
                        'bytes_out' => (int) ($r['bytes-out'] ?? 0),
                    ];
                }
                usort($usage, fn($a, $b) => ($b['bytes_in'] + $b['bytes_out']) <=> ($a['bytes_in'] + $a['bytes_out']));
                $usage = array_slice($usage, 0, 30);
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'Router belum dikonfigurasi — data pemakaian tidak tersedia.';
        }

        return view('reports.index', compact('summary', 'batches', 'usage', 'error'));
    }

    public function logs()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(50);
        return view('reports.logs', compact('logs'));
    }
}
