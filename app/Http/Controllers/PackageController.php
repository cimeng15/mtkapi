<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Package;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Throwable;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('name')->paginate(15);
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.form', ['package' => new Package(['shared_users' => 1, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $package = Package::create($data);
        ActivityLog::record('package.create', 'Menambah paket: '.$package->name);

        // Langsung kirim profil ke MikroTik (best-effort).
        $push = $this->pushToRouter($package);

        return redirect()->route('packages.index')
            ->with($push['ok'] ? 'success' : 'error', 'Paket berhasil ditambahkan. '.$push['message']);
    }

    public function edit(Package $package)
    {
        return view('packages.form', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $data = $this->validateData($request, $package->id);
        $package->update($data);
        ActivityLog::record('package.update', 'Mengubah paket: '.$package->name);

        // Perubahan langsung didorong ke MikroTik (best-effort).
        $push = $this->pushToRouter($package);

        return redirect()->route('packages.index')
            ->with($push['ok'] ? 'success' : 'error', 'Paket berhasil diperbarui. '.$push['message']);
    }

    /**
     * Dorong satu profil paket ke RouterOS. Tidak melempar error — kembalikan status.
     *
     * @return array{ok:bool, message:string}
     */
    protected function pushToRouter(Package $package): array
    {
        try {
            $mt = new MikrotikService();
            if (! $mt->hasSetting()) {
                return ['ok' => true, 'message' => '(Router belum dikonfigurasi — profil hanya tersimpan lokal.)'];
            }
            $mt->upsertProfile($package->mikrotik_profile, $package->rate_limit, $package->session_timeout, $package->shared_users);
            return ['ok' => true, 'message' => 'Profil "'.$package->mikrotik_profile.'" tersinkron ke MikroTik.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Namun gagal kirim ke router: '.$e->getMessage().' — klik Sinkron untuk mencoba lagi.'];
        }
    }

    public function destroy(Package $package)
    {
        $package->delete();
        ActivityLog::record('package.delete', 'Menghapus paket: '.$package->name);

        return back()->with('success', 'Paket dihapus.');
    }

    /**
     * Kirim/perbarui profil ini ke RouterOS hotspot.
     */
    public function sync(Package $package)
    {
        try {
            $mt = new MikrotikService();
            $mt->upsertProfile(
                $package->mikrotik_profile,
                $package->rate_limit,
                $package->session_timeout,
                $package->shared_users
            );
            ActivityLog::record('package.sync', 'Sinkron profil ke router: '.$package->mikrotik_profile);
            return back()->with('success', 'Profil "'.$package->mikrotik_profile.'" berhasil dikirim ke MikroTik.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal sinkron ke router: '.$e->getMessage());
        }
    }

    /**
     * Sinkron semua paket aktif ke RouterOS sekaligus.
     */
    public function syncAll()
    {
        $packages = Package::where('is_active', true)->get();
        if ($packages->isEmpty()) {
            return back()->with('error', 'Belum ada paket aktif untuk disinkron.');
        }

        try {
            $mt = new MikrotikService();
            foreach ($packages as $p) {
                $mt->upsertProfile($p->mikrotik_profile, $p->rate_limit, $p->session_timeout, $p->shared_users);
            }
            ActivityLog::record('package.sync_all', $packages->count().' profil disinkron ke router.');
            return back()->with('success', $packages->count().' paket berhasil disinkron ke MikroTik.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal sinkron: '.$e->getMessage());
        }
    }

    /**
     * Tarik user-profile yang sudah ada di RouterOS menjadi paket lokal.
     * Berguna bila router sekolah sudah punya profil sebelumnya — admin tinggal
     * melengkapi harga & keterangan.
     */
    public function importFromRouter()
    {
        try {
            $mt = new MikrotikService();
            $profiles = $mt->listProfiles();
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal membaca profil dari router: '.$e->getMessage());
        }

        $created = 0;
        $skipped = 0;
        foreach ($profiles as $prof) {
            $name = $prof['name'] ?? null;
            if (! $name) {
                continue;
            }
            if (Package::where('mikrotik_profile', $name)->exists()) {
                $skipped++;
                continue;
            }

            $timeout = $prof['session-timeout'] ?? null;
            if ($timeout === '0s' || $timeout === '00:00:00') {
                $timeout = null; // 0 = unlimited
            }

            // shared-users bisa berupa "unlimited" (non-angka) -> default 1
            $shared = $prof['shared-users'] ?? 1;
            $shared = is_numeric($shared) ? max(1, (int) $shared) : 1;

            Package::create([
                'name' => ucfirst($name),
                'mikrotik_profile' => $name,
                'rate_limit' => $prof['rate-limit'] ?? null,
                'session_timeout' => $timeout,
                'shared_users' => $shared,
                'price' => 0,
                'for_type' => 'umum',
                'description' => 'Diimpor dari MikroTik — lengkapi harga & keterangan.',
                'is_active' => true,
            ]);
            $created++;
        }

        ActivityLog::record('package.import_router', "$created profil diimpor dari router.");
        return back()->with('success', "Impor selesai: $created profil baru dibuat, $skipped sudah ada.");
    }

    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'mikrotik_profile' => 'required|string|max:100',
            'rate_limit' => 'nullable|string|max:191',
            'session_timeout' => 'nullable|string|max:100',
            'shared_users' => 'required|integer|min:1|max:100',
            'data_limit' => 'nullable|string|max:50',
            'price' => 'required|integer|min:0',
            'for_type' => 'required|in:umum,guru,siswa,staff',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
