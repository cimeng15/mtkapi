<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HotspotUserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MikrotikSettingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil akun sendiri (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---- Data Anggota: DIPISAH Siswa (students/siswa) & Guru (teachers/guru) ----
    // Akun hotspot dibuat otomatis saat import/tambah. Satu controller, dua scope.
    foreach ([
        ['uri' => 'siswa', 'name' => 'students'],
        ['uri' => 'guru',  'name' => 'teachers'],
    ] as $s) {
        Route::get($s['uri'].'/import', [MemberController::class, 'importForm'])->name($s['name'].'.import.form');
        Route::post($s['uri'].'/import', [MemberController::class, 'import'])->name($s['name'].'.import');
        Route::get($s['uri'].'/template', [MemberController::class, 'template'])->name($s['name'].'.template');
        Route::resource($s['uri'], MemberController::class)
            ->parameters([$s['uri'] => 'member'])
            ->names($s['name'])
            ->except(['show']);
    }

    // ---- User Hotspot & Monitoring ----
    Route::get('hotspot/monitor', [HotspotUserController::class, 'monitor'])->name('hotspot.monitor');
    Route::post('hotspot/disconnect', [HotspotUserController::class, 'disconnect'])->name('hotspot.disconnect');
    Route::get('hotspot', [HotspotUserController::class, 'index'])->name('hotspot.index');
    Route::post('hotspot', [HotspotUserController::class, 'store'])->name('hotspot.store');
    Route::post('hotspot/{hotspotUser}/sync', [HotspotUserController::class, 'sync'])->name('hotspot.sync');
    Route::post('hotspot/{hotspotUser}/toggle', [HotspotUserController::class, 'toggle'])->name('hotspot.toggle');
    Route::delete('hotspot/{hotspotUser}', [HotspotUserController::class, 'destroy'])->name('hotspot.destroy');

    // ---- Voucher ----
    Route::get('vouchers/print', [VoucherController::class, 'print'])->name('vouchers.print');
    Route::post('vouchers/generate', [VoucherController::class, 'generate'])->name('vouchers.generate');
    Route::delete('vouchers/batch', [VoucherController::class, 'destroyBatch'])->name('vouchers.destroy-batch');
    Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // ---- Paket / Profil ----
    Route::post('packages/sync-all', [PackageController::class, 'syncAll'])->name('packages.sync-all');
    Route::post('packages/import-router', [PackageController::class, 'importFromRouter'])->name('packages.import-router');
    Route::post('packages/{package}/sync', [PackageController::class, 'sync'])->name('packages.sync');
    Route::resource('packages', PackageController::class)->except(['show']);

    // ---- Laporan ----
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/logs', [ReportController::class, 'logs'])->name('reports.logs');

    // ---- Khusus Superadmin ----
    Route::middleware('superadmin')->group(function () {
        Route::get('settings/mikrotik', [MikrotikSettingController::class, 'edit'])->name('settings.mikrotik.edit');
        Route::put('settings/mikrotik', [MikrotikSettingController::class, 'update'])->name('settings.mikrotik.update');
        Route::post('settings/mikrotik/test', [MikrotikSettingController::class, 'test'])->name('settings.mikrotik.test');
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
