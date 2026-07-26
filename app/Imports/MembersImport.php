<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Package;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

/**
 * Import data guru/siswa dari Excel/CSV.
 *
 * Kolom yang dikenali (heading baris pertama, huruf kecil):
 *   member_id | id | nis | nisn | nip     -> ID login (username & password hotspot)
 *   name | nama                            -> nama lengkap
 *   type | tipe                            -> guru / siswa / staff (dibatasi oleh scope)
 *   package | paket | profile | profil     -> nama paket / profil kecepatan
 *   class | kelas                          -> kelas (siswa)
 *   department | jurusan | bagian          -> jurusan/bagian
 *   phone | hp | telepon                   -> nomor telepon
 *
 * Scope memisahkan data agar tidak tercampur:
 *   - 'siswa' : semua baris dipaksa type = siswa
 *   - 'guru'  : type guru (default) atau staff
 *   - null    : bebas (guru/siswa/staff sesuai kolom)
 *
 * Akun hotspot dibuat otomatis oleh controller setelah import.
 */
class MembersImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $imported = 0;

    /** Jumlah baris dilewati karena ID sudah terdaftar di scope lain (guru<->siswa). */
    public int $skippedCrossScope = 0;

    /** ID anggota yang berhasil diimpor (untuk provisioning akun hotspot). */
    public array $memberIds = [];

    /** Peta nama paket & nama profil (huruf kecil) -> package_id. */
    protected array $packageMap = [];

    /** Scope import: 'siswa' | 'guru' | null. */
    protected ?string $scope;

    public function __construct(?string $scope = null)
    {
        $this->scope = $scope;

        foreach (Package::all() as $p) {
            $this->packageMap[mb_strtolower(trim($p->name))] = $p->id;
            $this->packageMap[mb_strtolower(trim($p->mikrotik_profile))] = $p->id;
        }
    }

    public function model(array $row): ?Member
    {
        $memberId = $this->pick($row, ['member_id', 'id', 'nis', 'nisn', 'nip']);
        $name = $this->pick($row, ['name', 'nama']);

        if (empty($memberId) || empty($name)) {
            return null; // baris tak lengkap dilewati
        }

        // Jangan re-scope: kalau ID sudah ada di scope lain, lewati (jaga pemisahan data).
        if ($this->scope !== null) {
            $existing = Member::where('member_id', (string) $memberId)->first();
            if ($existing && ! in_array($existing->type, $this->scopeTypes(), true)) {
                $this->skippedCrossScope++;
                return null;
            }
        }

        $type = $this->resolveType($this->pick($row, ['type', 'tipe']));

        // Cocokkan kolom paket dengan nama paket / nama profil (case-insensitive)
        $packageId = null;
        $paketRaw = $this->pick($row, ['package', 'paket', 'profile', 'profil', 'paket_kecepatan']);
        if ($paketRaw !== null) {
            $packageId = $this->packageMap[mb_strtolower(trim((string) $paketRaw))] ?? null;
        }

        // updateOrCreate agar import ulang tidak menggandakan data
        $member = Member::updateOrCreate(
            ['member_id' => (string) $memberId],
            [
                'name' => (string) $name,
                'type' => $type,
                'package_id' => $packageId,
                'class' => $this->pick($row, ['class', 'kelas', 'rombel']),
                'department' => $this->pick($row, ['department', 'jurusan', 'bagian']),
                'phone' => $this->pick($row, ['phone', 'hp', 'telepon', 'no_hp']),
                'is_active' => true,
            ]
        );

        $this->imported++;
        $this->memberIds[] = $member->id;

        return null; // sudah disimpan manual; jangan disimpan ulang oleh Excel
    }

    /**
     * Tipe yang boleh untuk scope import saat ini.
     *
     * @return array<string>
     */
    protected function scopeTypes(): array
    {
        return match ($this->scope) {
            'siswa' => ['siswa'],
            'guru' => ['guru', 'staff'],
            default => ['guru', 'siswa', 'staff'],
        };
    }

    /**
     * Tentukan tipe anggota dengan menghormati scope import.
     */
    protected function resolveType($raw): string
    {
        $raw = strtolower(trim((string) $raw));

        if ($this->scope === 'siswa') {
            return 'siswa'; // semua dipaksa siswa
        }

        if ($this->scope === 'guru') {
            return $raw === 'staff' ? 'staff' : 'guru'; // guru default, staff bila disebut
        }

        // Tanpa scope: bebas sesuai kolom
        return in_array($raw, ['guru', 'siswa', 'staff'], true) ? $raw : 'siswa';
    }

    /**
     * Ambil nilai pertama yang ada dari beberapa kemungkinan nama kolom.
     */
    protected function pick(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }
}
