<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MikrotikSetting;
use App\Models\Package;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\MikrotikService;
use Throwable;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::with('package');
        if ($batch = $request->get('batch')) {
            $query->where('batch', $batch);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $vouchers = $query->latest()->paginate(30)->withQueryString();
        $batches = Voucher::select('batch')->whereNotNull('batch')->distinct()->pluck('batch');
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        return view('vouchers.index', compact('vouchers', 'batches', 'packages'));
    }

    /**
     * Generate voucher massal.
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'jumlah' => 'required|integer|min:1|max:500',
            'panjang_kode' => 'required|integer|min:4|max:12',
            'mode' => 'required|in:sama,beda',
            'prefix' => 'nullable|string|max:10',
        ]);

        $package = Package::findOrFail($data['package_id']);
        $batch = 'B'.now()->format('ymdHis');
        $prefix = strtoupper($data['prefix'] ?? '');
        $push = $request->boolean('push_router');

        $mt = $push ? new MikrotikService() : null;
        $routerError = null;
        $created = [];

        for ($i = 0; $i < $data['jumlah']; $i++) {
            $code = $prefix.$this->randomCode($data['panjang_kode']);
            // hindari tabrakan
            while (Voucher::where('username', $code)->exists()) {
                $code = $prefix.$this->randomCode($data['panjang_kode']);
            }
            $password = $data['mode'] === 'sama' ? $code : $this->randomCode($data['panjang_kode']);

            $voucher = Voucher::create([
                'package_id' => $package->id,
                'created_by' => $request->user()->id,
                'batch' => $batch,
                'username' => $code,
                'password' => $password,
                'price' => $package->price,
                'status' => 'unused',
                'comment' => 'voucher-'.$batch,
            ]);

            if ($mt) {
                try {
                    $id = $mt->addHotspotUser($code, $password, $package->mikrotik_profile, 'voucher-'.$batch);
                    $voucher->update(['synced' => true, 'mikrotik_id' => $id]);
                } catch (Throwable $e) {
                    $routerError = $e->getMessage();
                    $mt = null; // hentikan percobaan push berikutnya
                }
            }

            $created[] = $voucher;
        }

        ActivityLog::record('voucher.generate', count($created).' voucher batch '.$batch.' ('.$package->name.')');

        $msg = count($created).' voucher berhasil dibuat (batch '.$batch.').';
        if ($routerError) {
            $msg .= ' Catatan: sebagian gagal dikirim ke router — '.$routerError;
        }

        return redirect()->route('vouchers.index', ['batch' => $batch])
            ->with($routerError ? 'error' : 'success', $msg);
    }

    /**
     * Cetak voucher (PDF) berdasarkan batch.
     */
    public function print(Request $request)
    {
        $batch = $request->get('batch');
        $query = Voucher::with('package');
        if ($batch) {
            $query->where('batch', $batch);
        } elseif ($ids = $request->get('ids')) {
            $query->whereIn('id', explode(',', $ids));
        }

        $vouchers = $query->get();
        if ($vouchers->isEmpty()) {
            return back()->with('error', 'Tidak ada voucher untuk dicetak.');
        }

        $setting = MikrotikSetting::active();
        $portal = $setting?->dns_name ?: $setting?->host ?: 'hotspot.sekolah.id';

        $pdf = Pdf::loadView('vouchers.print', compact('vouchers', 'portal', 'batch'))
            ->setPaper('a4');

        return $pdf->stream('voucher-'.($batch ?: 'cetak').'.pdf');
    }

    public function destroy(Voucher $voucher)
    {
        try {
            $mt = new MikrotikService();
            if ($mt->hasSetting()) {
                $existing = $mt->findHotspotUser($voucher->username);
                if ($existing) {
                    $mt->removeHotspotUser($existing['.id']);
                }
            }
        } catch (Throwable $e) {
            // abaikan, tetap hapus lokal
        }
        $voucher->delete();
        return back()->with('success', 'Voucher dihapus.');
    }

    public function destroyBatch(Request $request)
    {
        $request->validate(['batch' => 'required|string']);
        $count = Voucher::where('batch', $request->batch)->count();
        Voucher::where('batch', $request->batch)->delete();
        ActivityLog::record('voucher.delete_batch', "Hapus batch {$request->batch} ($count voucher)");

        return redirect()->route('vouchers.index')->with('success', "Batch dihapus ($count voucher).");
    }

    protected function randomCode(int $len): string
    {
        // hindari karakter ambigu (0/O, 1/I/l)
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $out;
    }
}
