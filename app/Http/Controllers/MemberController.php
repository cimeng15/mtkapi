<?php

namespace App\Http\Controllers;

use App\Imports\MembersImport;
use App\Models\ActivityLog;
use App\Models\HotspotUser;
use App\Models\Member;
use App\Models\Package;
use App\Services\MikrotikService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Mengelola data anggota (guru & siswa) yang DIPISAH menjadi dua menu:
 *  - scope "siswa"  (route students.*, URI /siswa)  -> type: siswa
 *  - scope "guru"   (route teachers.*, URI /guru)    -> type: guru & staff
 *
 * Skema pembuatan akun hotspot tetap sama untuk keduanya (otomatis saat
 * tambah/import, username & password = ID login, paket menentukan kecepatan).
 */
class MemberController extends Controller
{
    public function index(Request $request)
    {
        $scope = $this->scopeConfig($this->currentScope($request));

        $query = Member::query()->with(['hotspotUser', 'package'])
            ->whereIn('type', $scope['types']);

        if ($search = $request->get('q')) {
            $query->where(function ($w) use ($search) {
                $w->where('member_id', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%")
                  ->orWhere('class', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%");
            });
        }
        // Sub-filter tipe (khusus scope guru: guru / staff)
        if (($type = $request->get('type')) && in_array($type, $scope['types'])) {
            $query->where('type', $type);
        }

        $members = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('members.index', compact('members', 'scope'));
    }

    public function create(Request $request)
    {
        $scope = $this->scopeConfig($this->currentScope($request));

        return view('members.form', [
            'member' => new Member(['type' => $scope['default'], 'is_active' => true]),
            'packages' => $this->activePackages(),
            'scope' => $scope,
        ]);
    }

    public function store(Request $request)
    {
        $scope = $this->scopeConfig($this->currentScope($request));
        $data = $this->validateData($request, $scope);
        $member = Member::create($data);

        // Akun hotspot dibuat OTOMATIS begitu anggota ditambahkan.
        $prov = $this->autoProvision(collect([$member->load('package')]));

        ActivityLog::record('member.create', 'Menambah '.$scope['singular'].': '.$member->name);

        return redirect()->route($scope['route'].'.index')
            ->with($prov['failed'] ? 'error' : 'success', 'Data '.$scope['singular'].' ditambahkan. '.$prov['message']);
    }

    public function edit(Request $request, Member $member)
    {
        $scope = $this->scopeConfig($this->currentScope($request));
        $this->ensureInScope($member, $scope);

        return view('members.form', [
            'member' => $member,
            'packages' => $this->activePackages(),
            'scope' => $scope,
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $scope = $this->scopeConfig($this->currentScope($request));
        $this->ensureInScope($member, $scope);

        $data = $this->validateData($request, $scope, $member->id);
        $oldPackageId = $member->package_id;
        $member->update($data);
        ActivityLog::record('member.update', 'Mengubah '.$scope['singular'].': '.$member->name);

        $note = '';
        $member->load(['hotspotUser', 'package']);

        if (! $member->hotspotUser) {
            // Belum punya akun (mis. data lama) -> buatkan otomatis
            $prov = $this->autoProvision(collect([$member]));
            $note = $prov['message'];
        } elseif ($oldPackageId !== $member->package_id) {
            // Paket berubah -> perbarui akun hotspot & profil di router
            $note = $this->applyPackageChange($member);
        }

        return redirect()->route($scope['route'].'.index')->with('success', 'Data '.$scope['singular'].' diperbarui. '.$note);
    }

    public function destroy(Request $request, Member $member)
    {
        $scope = $this->scopeConfig($this->currentScope($request));
        $this->ensureInScope($member, $scope);

        // Hapus akun hotspot di router bila ada, lalu hapus data
        if ($member->hotspotUser) {
            $this->removeHotspotEverywhere($member->hotspotUser);
        }
        $member->delete();
        ActivityLog::record('member.delete', 'Menghapus '.$scope['singular'].': '.$member->name);

        return back()->with('success', 'Data '.$scope['singular'].' & akun hotspotnya dihapus.');
    }

    // ==================== IMPORT ====================

    public function importForm(Request $request)
    {
        $scope = $this->scopeConfig($this->currentScope($request));

        return view('members.import', [
            'packages' => $this->activePackages(),
            'scope' => $scope,
        ]);
    }

    public function import(Request $request)
    {
        $scope = $this->scopeConfig($this->currentScope($request));

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        try {
            // Import di-scope: siswa dipaksa type siswa; guru menerima guru & staff.
            $import = new MembersImport($scope['scope']);

            // Salin file upload ke path sementara dengan ekstensi asli.
            // Menghindari "Path cannot be empty" saat getRealPath() false di Windows.
            $upload = $request->file('file');
            $tempPath = storage_path('framework/cache/laravel-excel/import_'.uniqid().'.'.$upload->getClientOriginalExtension());
            copy($upload->getPathname(), $tempPath);

            try {
                Excel::import($import, $tempPath);
            } finally {
                @unlink($tempPath);
            }

            // Buat akun hotspot OTOMATIS untuk semua anggota hasil import yang belum punya.
            $members = Member::with(['package', 'hotspotUser'])
                ->whereIn('id', $import->memberIds)
                ->get();
            $prov = $this->autoProvision($members);

            ActivityLog::record('member.import', $import->imported.' '.$scope['singular'].' diimpor, '.$prov['created'].' akun hotspot dibuat.');

            $failures = count($import->errors());
            $msg = "Impor selesai: {$import->imported} {$scope['singular']}. Akun hotspot: {$prov['created']} dibuat, {$prov['skipped']} sudah ada.";
            if ($prov['failed']) {
                $msg .= " {$prov['failed']} gagal ke router ({$prov['error']}).";
            }
            if ($import->skippedCrossScope) {
                $msg .= " {$import->skippedCrossScope} dilewati (ID terdaftar di menu lain).";
            }
            if ($failures) {
                $msg .= " ($failures baris dilewati).";
            }

            return redirect()->route($scope['route'].'.index')->with($prov['failed'] ? 'error' : 'success', $msg);
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    /**
     * Unduh template CSV import sesuai scope (siswa / guru).
     */
    public function template(Request $request): StreamedResponse
    {
        $scope = $this->scopeConfig($this->currentScope($request));
        $filename = 'template_import_'.$scope['scope'].'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($scope) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // BOM agar Excel mengenali UTF-8
            fputcsv($out, ['member_id', 'name', 'type', 'package', 'class', 'department', 'phone'], ';');

            if ($scope['scope'] === 'siswa') {
                $paket = Package::where('for_type', 'siswa')->value('name') ?? 'Siswa 3 Jam';
                fputcsv($out, ['2024001', 'Budi Santoso', 'siswa', $paket, 'X IPA 1', '', '081234567890'], ';');
                fputcsv($out, ['2024002', 'Ani Lestari', 'siswa', $paket, 'X IPA 1', '', '081200000002'], ';');
            } else {
                $paket = Package::where('for_type', 'guru')->value('name') ?? 'Guru Unlimited';
                fputcsv($out, ['198501012010011001', 'Siti Aminah, S.Pd', 'guru', $paket, '', 'Matematika', '081298765432'], ';');
                fputcsv($out, ['199003032015012002', 'Rudi Hartono', 'staff', $paket, '', 'Tata Usaha', '081211112222'], ';');
            }
            fclose($out);
        }, 200, $headers);
    }

    // ==================== SCOPE HELPERS ====================

    /**
     * Tentukan scope aktif dari nama route (teachers.* -> guru, selain itu siswa).
     */
    protected function currentScope(Request $request): string
    {
        return $request->routeIs('teachers.*') ? 'guru' : 'siswa';
    }

    /**
     * Pastikan anggota memang milik scope URL saat ini. Mencegah mengelola
     * guru lewat URL /siswa (dan sebaliknya) — data guru & siswa tetap terpisah.
     */
    protected function ensureInScope(Member $member, array $scope): void
    {
        if (! in_array($member->type, $scope['types'], true)) {
            abort(404);
        }
    }

    /**
     * Konfigurasi tampilan & aturan tiap scope.
     *
     * @return array{scope:string, types:array<string>, default:string, label:string, singular:string, route:string, detail:string, icon:string, color:string}
     */
    protected function scopeConfig(string $scope): array
    {
        if ($scope === 'guru') {
            return [
                'scope' => 'guru',
                'types' => ['guru', 'staff'],
                'default' => 'guru',
                'label' => 'Data Guru & Tendik',
                'singular' => 'Guru',
                'route' => 'teachers',
                'detail' => 'Jurusan/Bagian',
                'icon' => 'person-badge',
                'color' => 'success',
            ];
        }

        return [
            'scope' => 'siswa',
            'types' => ['siswa'],
            'default' => 'siswa',
            'label' => 'Data Siswa',
            'singular' => 'Siswa',
            'route' => 'students',
            'detail' => 'Kelas',
            'icon' => 'mortarboard',
            'color' => 'info',
        ];
    }

    // ==================== PROVISIONING OTOMATIS ====================

    /**
     * Buat akun hotspot otomatis untuk kumpulan anggota.
     * Username & password = ID anggota. Profil di router dipastikan ada
     * satu kali per paket agar efisien.
     *
     * @return array{created:int, skipped:int, failed:int, error:?string, message:string}
     */
    protected function autoProvision(Collection $members): array
    {
        // Pastikan tiap profil paket ada di router SEKALI saja
        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                $members->pluck('package')->filter()->unique('id')->each(function ($p) use ($mt) {
                    $mt->upsertProfile($p->mikrotik_profile, $p->rate_limit, $p->session_timeout, $p->shared_users);
                });
            }
        } catch (Throwable $e) {
            // diabaikan; tiap akun tetap dicoba & error dilaporkan per akun
        }

        $created = 0; $skipped = 0; $failed = 0; $error = null;
        foreach ($members as $member) {
            $r = $this->provisionHotspot($member, $member->package, false);
            if ($r['ok']) {
                $created++;
            } elseif ($r['skipped'] ?? false) {
                $skipped++;
            } else {
                $failed++;
                $error = $r['message'];
            }
        }

        $message = "Akun hotspot: $created dibuat".($skipped ? ", $skipped sudah ada" : '').'.';

        return compact('created', 'skipped', 'failed', 'error', 'message');
    }

    /**
     * Buat satu akun hotspot dari anggota. Paket boleh null (pakai profil default router).
     */
    protected function provisionHotspot(Member $member, ?Package $package, bool $ensureProfile = true): array
    {
        if ($member->hotspotUser) {
            return ['ok' => false, 'skipped' => true, 'message' => 'Akun untuk '.$member->name.' sudah ada.'];
        }

        $hotspot = HotspotUser::create([
            'member_id' => $member->id,
            'package_id' => $package?->id,
            'username' => $member->member_id,
            'password' => $member->member_id,
            'comment' => $member->name.' ('.$member->type.')',
            'status' => 'active',
        ]);

        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                if ($package && $ensureProfile) {
                    $mt->upsertProfile($package->mikrotik_profile, $package->rate_limit, $package->session_timeout, $package->shared_users);
                }
                $id = $mt->addHotspotUser(
                    $hotspot->username,
                    $hotspot->password,
                    $package?->mikrotik_profile,
                    $hotspot->comment,
                    $this->parseDataLimit($package?->data_limit)
                );
                $hotspot->update(['synced' => true, 'synced_at' => now(), 'mikrotik_id' => $id]);
            }
        } catch (Throwable $e) {
            return ['ok' => true, 'skipped' => false, 'message' => 'Akun '.$member->name.' dibuat lokal, gagal ke router: '.$e->getMessage()];
        }

        return ['ok' => true, 'skipped' => false, 'message' => 'Akun '.$member->name.' dibuat.'];
    }

    /**
     * Terapkan perubahan paket ke akun hotspot yang sudah ada (lokal + router).
     */
    protected function applyPackageChange(Member $member): string
    {
        $hotspot = $member->hotspotUser;
        $package = $member->package;
        $hotspot->update(['package_id' => $package?->id, 'synced' => false]);

        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                if ($package) {
                    $mt->upsertProfile($package->mikrotik_profile, $package->rate_limit, $package->session_timeout, $package->shared_users);
                }
                $existing = $mt->findHotspotUser($hotspot->username);
                if ($existing) {
                    $mt->updateHotspotUser($existing['.id'], ['profile' => $package?->mikrotik_profile ?: 'default']);
                    $hotspot->update(['synced' => true, 'synced_at' => now(), 'mikrotik_id' => $existing['.id']]);
                }
            }
            return 'Paket akun diperbarui.';
        } catch (Throwable $e) {
            return 'Paket diubah lokal, gagal ke router: '.$e->getMessage();
        }
    }

    protected function removeHotspotEverywhere(HotspotUser $hotspot): void
    {
        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                $existing = $mt->findHotspotUser($hotspot->username);
                if ($existing) {
                    $mt->removeHotspotUser($existing['.id']);
                }
            }
        } catch (Throwable $e) {
            // tetap lanjut hapus lokal
        }
        $hotspot->delete();
    }

    protected function parseDataLimit(?string $limit): ?string
    {
        if (empty($limit)) {
            return null;
        }
        $limit = strtoupper(trim($limit));
        $mult = ['K' => 1024, 'M' => 1048576, 'G' => 1073741824];
        if (preg_match('/^(\d+)\s*([KMG])?B?$/', $limit, $m)) {
            $bytes = (int) $m[1];
            if (! empty($m[2])) {
                $bytes *= $mult[$m[2]];
            }
            return (string) $bytes;
        }
        return null;
    }

    protected function activePackages()
    {
        return Package::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Validasi + paksa tipe agar sesuai scope (mencegah data tercampur).
     */
    protected function validateData(Request $request, array $scope, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'member_id' => 'required|string|max:50|unique:members,member_id'.($ignoreId ? ",$ignoreId" : ''),
            'name' => 'required|string|max:150',
            'type' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
            'class' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        // Kunci tipe ke scope (mencegah data tercampur): siswa selalu siswa;
        // guru menerima guru/staff, selain itu dipaksa ke default scope.
        $data['type'] = in_array($request->input('type'), $scope['types'], true)
            ? $request->input('type')
            : $scope['default'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
