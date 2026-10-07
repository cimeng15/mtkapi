<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Throwable;

class BandwidthController extends Controller
{
    /**
     * Halaman monitoring bandwidth (view + Chart.js).
     */
    public function index()
    {
        $interfaces = [];
        $error = null;
        $mt = new MikrotikService();

        if (! $mt->hasSetting()) {
            $error = 'Belum ada konfigurasi router aktif.';
        } else {
            try {
                $interfaces = collect($mt->interfaces())
                    ->filter(fn ($i) => ($i['type'] ?? '') !== 'loopback')
                    ->values()
                    ->all();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('bandwidth.index', compact('interfaces', 'error'));
    }

    /**
     * JSON endpoint — dipanggil oleh Chart.js setiap N detik.
     * Mengembalikan: total traffic per interface + top users bandwidth.
     */
    public function poll(): JsonResponse
    {
        try {
            $mt = new MikrotikService();
            if (! $mt->hasSetting()) {
                return response()->json(['error' => 'Router belum dikonfigurasi.'], 422);
            }

            $interfaces = collect($mt->interfaces())
                ->filter(fn ($i) => ($i['type'] ?? '') !== 'loopback')
                ->map(fn ($i) => [
                    'name'    => $i['name'] ?? '-',
                    'type'    => $i['type'] ?? '-',
                    'running' => ($i['running'] ?? 'false') === 'true',
                    'tx'      => (int) ($i['tx-byte'] ?? 0),
                    'rx'      => (int) ($i['rx-byte'] ?? 0),
                ])
                ->values();

            $users = collect($mt->activeUsersTraffic())
                ->take(20)
                ->map(fn ($u) => [
                    'user'    => $u['user'] ?? '-',
                    'address' => $u['address'] ?? '-',
                    'uptime'  => $u['uptime'] ?? '-',
                    'rx'      => (int) ($u['bytes-in'] ?? 0),  // download = bytes-in dari perspektif router
                    'tx'      => (int) ($u['bytes-out'] ?? 0), // upload
                ])
                ->values();

            $resource = $mt->resource();

            return response()->json([
                'time'       => now()->format('H:i:s'),
                'interfaces' => $interfaces,
                'users'      => $users,
                'cpu'        => (int) ($resource['cpu-load'] ?? 0),
                'mem_used'   => (int) ($resource['total-memory'] ?? 0) - (int) ($resource['free-memory'] ?? 0),
                'mem_total'  => (int) ($resource['total-memory'] ?? 0),
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
