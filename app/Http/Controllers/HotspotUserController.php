<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HotspotUser;
use App\Models\Package;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Throwable;

class HotspotUserController extends Controller
{
    public function index(Request $request)
    {
        $query = HotspotUser::with(['member', 'package']);
        if ($search = $request->get('q')) {
            $query->where('username', 'like', "%$search%")
                  ->orWhere('comment', 'like', "%$search%");
        }
        $users = $query->latest()->paginate(20)->withQueryString();
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        return view('hotspot.index', compact('users', 'packages'));
    }

    /**
     * Monitoring: daftar sesi yang sedang online di router.
     */
    public function monitor()
    {
        $active = [];
        $error = null;
        $mt = new MikrotikService();
        if (! $mt->hasSetting()) {
            $error = 'Belum ada konfigurasi router aktif.';
        } else {
            try {
                $active = $mt->activeUsers();
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('hotspot.monitor', compact('active', 'error'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:hotspot_users,username',
            'password' => 'required|string|max:100',
            'package_id' => 'required|exists:packages,id',
            'comment' => 'nullable|string|max:150',
        ]);

        $package = Package::find($data['package_id']);
        $hotspot = HotspotUser::create($data + ['status' => 'active']);

        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                // Pastikan profil paket ada di router sebelum menambah user
                $mt->upsertProfile($package->mikrotik_profile, $package->rate_limit, $package->session_timeout, $package->shared_users);
                $id = $mt->addHotspotUser($data['username'], $data['password'], $package->mikrotik_profile, $data['comment'] ?? null);
                $hotspot->update(['synced' => true, 'synced_at' => now(), 'mikrotik_id' => $id]);
            }
        } catch (Throwable $e) {
            return back()->with('error', 'Akun disimpan lokal, gagal ke router: '.$e->getMessage());
        }

        ActivityLog::record('hotspot.create', 'Buat user hotspot: '.$data['username']);
        return back()->with('success', 'User hotspot berhasil dibuat.');
    }

    /**
     * Sinkron ulang satu akun ke router.
     */
    public function sync(HotspotUser $hotspotUser)
    {
        try {
            $mt = new MikrotikService();
            $existing = $mt->findHotspotUser($hotspotUser->username);
            if ($existing) {
                $mt->updateHotspotUser($existing['.id'], [
                    'password' => $hotspotUser->password,
                    'profile' => $hotspotUser->package?->mikrotik_profile ?: 'default',
                ]);
                $id = $existing['.id'];
            } else {
                $id = $mt->addHotspotUser(
                    $hotspotUser->username,
                    $hotspotUser->password,
                    $hotspotUser->package?->mikrotik_profile,
                    $hotspotUser->comment
                );
            }
            $hotspotUser->update(['synced' => true, 'synced_at' => now(), 'mikrotik_id' => $id]);
            return back()->with('success', 'Akun '.$hotspotUser->username.' tersinkron ke router.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal sinkron: '.$e->getMessage());
        }
    }

    public function toggle(HotspotUser $hotspotUser)
    {
        $newStatus = $hotspotUser->status === 'active' ? 'disabled' : 'active';
        $hotspotUser->update(['status' => $newStatus]);

        try {
            $mt = new MikrotikService();
            $existing = $mt->findHotspotUser($hotspotUser->username);
            if ($existing) {
                $mt->setHotspotUserDisabled($existing['.id'], $newStatus === 'disabled');
            }
        } catch (Throwable $e) {
            return back()->with('error', 'Status lokal diubah, gagal ke router: '.$e->getMessage());
        }

        return back()->with('success', 'Akun '.$hotspotUser->username.' kini '.($newStatus === 'active' ? 'aktif' : 'dinonaktifkan').'.');
    }

    public function destroy(HotspotUser $hotspotUser)
    {
        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                $existing = $mt->findHotspotUser($hotspotUser->username);
                if ($existing) {
                    $mt->removeHotspotUser($existing['.id']);
                }
            }
        } catch (Throwable $e) {
            // tetap hapus lokal walau router gagal
        }

        $username = $hotspotUser->username;
        $hotspotUser->delete();
        ActivityLog::record('hotspot.delete', 'Hapus user hotspot: '.$username);

        return back()->with('success', 'User hotspot '.$username.' dihapus.');
    }

    /**
     * Putuskan sesi aktif (dari halaman monitoring).
     */
    public function disconnect(Request $request)
    {
        $request->validate(['active_id' => 'required|string']);
        try {
            $mt = new MikrotikService();
            $mt->disconnectActive($request->active_id);
            ActivityLog::record('hotspot.disconnect', 'Putus sesi: '.$request->get('username', $request->active_id));
            return back()->with('success', 'Sesi berhasil diputus.');
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memutus sesi: '.$e->getMessage());
        }
    }
}
