# Manajemen Hotspot MikroTik Sekolah

Aplikasi web (Laravel 11) untuk mengelola hotspot MikroTik di lingkungan sekolah:
login multi-admin, import data guru/siswa, pembuatan akun hotspot dari ID (NIS/NISN/NIP),
generate & cetak voucher, monitoring sesi aktif, manajemen paket/profil, dan laporan.

## Fitur

- **Login multi-admin** — peran *Superadmin* (akses penuh) & *Operator* (tanpa menu sistem).
- **Data Guru/Siswa** — CRUD + **import Excel/CSV** (ID login = NIS/NISN/NIP).
- **Akun hotspot dari ID** — buat akun (username & password = ID anggota), satuan atau massal.
- **User Hotspot** — kelola akun, aktif/nonaktif, sinkron ke router, hapus.
- **Voucher** — generate massal (kode acak, prefix, batch), cetak PDF, hapus per batch.
- **Paket / Profil** — atur rate-limit, durasi, shared-users, harga; sinkron ke `user-profile` RouterOS.
- **Monitoring Sesi** — daftar pengguna online + putus sesi (auto-refresh).
- **Laporan** — rekap voucher per batch, pemakaian data dari router, log aktivitas.
- **Pengaturan Router** — konfigurasi koneksi RouterOS API (password terenkripsi).

## Teknologi

- Laravel 11, PHP 8.3, MySQL 8
- [evilfreelancer/routeros-api-php](https://github.com/EvilFreelancer/routeros-api-php) — RouterOS API
- barryvdh/laravel-dompdf — cetak voucher PDF
- maatwebsite/excel — import data
- Laravel Breeze (Blade) — autentikasi; UI admin dengan Bootstrap 5

## Menjalankan (Laragon)

```bash
# PHP & Composer dari Laragon sudah tersedia
php artisan migrate --seed      # buat tabel + data awal
php artisan serve               # http://127.0.0.1:8000
```

Atau tambahkan virtual host Laragon yang mengarah ke folder `public/`.

### Akun default (dari seeder)

| Peran       | Email                | Password   |
|-------------|----------------------|------------|
| Superadmin  | admin@sekolah.id     | `password` |
| Operator    | operator@sekolah.id  | `password` |

> **Ganti password default** setelah login pertama (menu Profil).

## Konfigurasi MikroTik

1. Di RouterOS, aktifkan service API:
   ```
   /ip service enable api
   /ip service set api port=8728
   ```
2. Buat user API khusus (disarankan) dengan grup yang cukup (mis. `full` atau grup kustom
   berisi izin `api`, `read`, `write`, `policy`).
3. Login aplikasi sebagai Superadmin → **Pengaturan Router** → isi Host/IP, port, user, password → **Simpan** → **Tes Koneksi**.

Pengaturan awal juga bisa lewat `.env`:

```
MIKROTIK_HOST=192.168.88.1
MIKROTIK_USER=admin
MIKROTIK_PASS=
MIKROTIK_PORT=8728
MIKROTIK_SSL=false
```

## Format Import Data Anggota

Baris pertama file = header. Kolom dikenali (alias huruf kecil):

| Kolom | Alias | Wajib |
|-------|-------|-------|
| `member_id` | `id`, `nis`, `nisn`, `nip` | Ya |
| `name` | `nama` | Ya |
| `type` | `tipe` (guru/siswa/staff) | Tidak |
| `class` | `kelas`, `rombel` | Tidak |
| `department` | `jurusan`, `bagian` | Tidak |
| `phone` | `hp`, `telepon` | Tidak |

Unduh contoh via tombol **Template** di halaman Data Guru/Siswa. Import ulang ID yang sama akan
*memperbarui* data (tidak menggandakan).

## Alur Kerja Umum

1. **Pengaturan Router** → hubungkan ke MikroTik.
2. **Paket / Profil** → buat paket, klik *Sinkron* agar profil dibuat di router.
3. **Data Guru/Siswa** → import Excel/CSV.
4. Centang anggota → pilih paket → **Buat Akun** (massal). Akun otomatis terdorong ke router.
5. **Voucher** → generate untuk tamu/umum, cetak PDF.
6. **Monitoring & Laporan** → pantau pemakaian.

## Catatan Keamanan

- Password router disimpan **terenkripsi** (Laravel Crypt).
- Pendaftaran publik dinonaktifkan; admin baru dibuat oleh Superadmin di menu **Kelola Admin**.
- Semua aksi penting tercatat di **Log Aktivitas**.
