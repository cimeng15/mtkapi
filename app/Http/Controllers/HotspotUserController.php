<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HotspotUser;
use App\Models\Member;
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
            $query->where(function ($w) use ($search) {
                $w->where('username', 'like', "%$search%")
                  ->orWhere('comment', 'like', "%$search%");
            });
        }
        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100]) ? (int) $request->get('per_page') : 20;

        // Sorting
        $sortable = ['username', 'password', 'profile', 'status', 'created_at'];
        $sort = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $users = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        if ($request->ajax()) {
            return view('hotspot._table', compact('users', 'perPage', 'sort', 'dir'));
        }

        return view('hotspot.index', compact('users', 'packages', 'perPage', 'sort', 'dir'));
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

    public function batch(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('hotspot.index');
        }

        $ids = $request->input('ids', []);
        $action = $request->input('batch_action');
        $users = HotspotUser::whereIn('id', $ids)->get();

        if ($action === 'delete') {
            foreach ($users as $h) {
                try {
                    $mt = new MikrotikService();
                    if ($mt->hasSetting()) {
                        $existing = $mt->findHotspotUser($h->username);
                        if ($existing) $mt->removeHotspotUser($existing['.id']);
                    }
                } catch (Throwable $e) {}
                $h->delete();
            }
            ActivityLog::record('hotspot.batch_delete', 'Batch hapus '.count($users).' user hotspot');
            return back()->with('success', count($users).' user hotspot dihapus.');
        }

        if ($action === 'toggle') {
            foreach ($users as $h) {
                $new = $h->status === 'active' ? 'disabled' : 'active';
                $h->update(['status' => $new]);
                try {
                    $mt = new MikrotikService();
                    if ($mt->hasSetting()) {
                        $existing = $mt->findHotspotUser($h->username);
                        if ($existing) $mt->setHotspotUserDisabled($existing['.id'], $new === 'disabled');
                    }
                } catch (Throwable $e) {}
            }
            return back()->with('success', count($users).' user hotspot diubah statusnya.');
        }

        if ($action === 'sync') {
            $ok = 0;
            foreach ($users as $h) {
                try {
                    $mt = new MikrotikService();
                    $existing = $mt->findHotspotUser($h->username);
                    if ($existing) {
                        $mt->updateHotspotUser($existing['.id'], [
                            'password' => $h->password,
                            'profile' => $h->package?->mikrotik_profile ?: 'default',
                        ]);
                        $id = $existing['.id'];
                    } else {
                        $id = $mt->addHotspotUser(
                            $h->username, $h->password,
                            $h->package?->mikrotik_profile, $h->comment
                        );
                    }
                    $h->update(['synced' => true, 'synced_at' => now(), 'mikrotik_id' => $id]);
                    $ok++;
                } catch (Throwable $e) {}
            }
            return back()->with('success', $ok.' dari '.count($users).' user hotspot tersinkron.');
        }

        return back()->with('error', 'Aksi tidak dikenal.');
    }

    /**
     * Tarik (import) semua user hotspot dari router MikroTik ke database lokal.
     * User yang sudah ada (by username) di-skip, profile dicocokkan ke paket lokal.
     * Comment dipakai untuk deteksi tipe (guru/siswa) dan nama, lalu otomatis
     * buat record Member sehingga muncul di Data Siswa / Data Guru.
     */
    public function importFromRouter(Request $request)
    {
        try {
            $mt = new MikrotikService();
            if (! $mt->hasSetting()) {
                return back()->with('error', 'Belum ada konfigurasi router aktif.');
            }

            $routerUsers = $mt->listHotspotUsers();
            $existingUsernames = HotspotUser::pluck('username')->flip();
            $existingMemberIds = Member::pluck('member_id')->flip();

            // Cache profile→package mapping
            $packages = Package::all()->keyBy('mikrotik_profile');

            $imported = 0;
            $skipped = 0;
            $noProfile = 0;
            $membersCreated = 0;

            foreach ($routerUsers as $ru) {
                $username = $ru['name'] ?? null;
                if (! $username || $username === 'default-trial') {
                    continue;
                }

                // Skip jika sudah ada di DB
                if ($existingUsernames->has($username)) {
                    $skipped++;
                    continue;
                }

                $profile = $ru['profile'] ?? 'default';
                $package = $packages->get($profile);
                $disabled = ($ru['disabled'] ?? 'false') === 'true';
                $comment = $ru['comment'] ?? '';

                // Deteksi tipe dari comment: cari kata guru/staff/siswa
                $type = $this->detectTypeFromComment($comment);
                // Ekstrak nama dari comment: hapus "(guru)", "(siswa)", dll
                $name = $this->extractNameFromComment($comment, $username);

                // Buat Member jika belum ada (by member_id = username)
                $member = null;
                if (! $existingMemberIds->has($username)) {
                    $member = Member::create([
                        'member_id'  => $username,
                        'name'       => $name,
                        'type'       => $type,
                        'package_id' => $package?->id,
                        'is_active'  => ! $disabled,
                    ]);
                    $existingMemberIds->put($username, true);
                    $membersCreated++;
                } else {
                    $member = Member::where('member_id', $username)->first();
                }

                $hotspot = HotspotUser::create([
                    'username'    => $username,
                    'password'    => $ru['password'] ?? $username,
                    'package_id'  => $package?->id,
                    'member_id'   => $member?->id,
                    'comment'     => $comment ?: null,
                    'status'      => $disabled ? 'disabled' : 'active',
                    'synced'      => true,
                    'synced_at'   => now(),
                    'mikrotik_id' => $ru['.id'] ?? null,
                ]);

                if (! $package) {
                    $noProfile++;
                }
                $imported++;
            }

            ActivityLog::record('hotspot.import_router', "Import dari router: {$imported} user, {$membersCreated} anggota baru");

            $msg = "{$imported} user berhasil diimpor dari router.";
            if ($membersCreated) {
                $msg .= " {$membersCreated} data anggota (guru/siswa) otomatis dibuat.";
            }
            if ($skipped) {
                $msg .= " {$skipped} sudah ada (di-skip).";
            }
            if ($noProfile) {
                $msg .= " {$noProfile} user tanpa paket cocok.";
            }

            return back()->with('success', $msg);
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal import dari router: '.$e->getMessage());
        }
    }

    /**
     * Deteksi tipe anggota dari comment MikroTik.
     * Mencari kata 'guru', 'staff', atau 'siswa' (case-insensitive).
     */
    protected function detectTypeFromComment(string $comment): string
    {
        $lower = mb_strtolower($comment);
        if (str_contains($lower, 'guru')) {
            return 'guru';
        }
        if (str_contains($lower, 'staff') || str_contains($lower, 'tendik') || str_contains($lower, 'tata usaha') || str_contains($lower, 'tu')) {
            return 'staff';
        }
        // Default siswa
        return 'siswa';
    }

    /**
     * Ekstrak nama dari comment. Hapus penanda tipe seperti "(guru)", "(siswa)", dll.
     * Jika comment kosong, gunakan username sebagai nama.
     */
    protected function extractNameFromComment(string $comment, string $fallback): string
    {
        if (empty(trim($comment))) {
            return $fallback;
        }
        // Hapus pola "(guru)", "(siswa)", "(staff)", "(tendik)" dll
        $name = preg_replace('/\s*\(?\b(guru|siswa|staff|tendik|tata\s*usaha|tu)\b\)?\s*/i', ' ', $comment);
        $name = trim(preg_replace('/\s+/', ' ', $name));
        return $name ?: $fallback;
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
