<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Voucher {{ $batch }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size:10px; }
        .grid { display:block; }
        .voucher {
            display:inline-block; width:31%; margin:0 1% 8px 0; vertical-align:top;
            border:1px dashed #333; border-radius:6px; padding:8px; text-align:center;
        }
        .voucher .title { font-weight:bold; font-size:11px; margin-bottom:2px; }
        .voucher .portal { font-size:8px; color:#555; margin-bottom:4px; }
        .voucher .cred { font-size:9px; margin:2px 0; }
        .voucher .code { font-family: DejaVu Sans Mono, monospace; font-size:14px; font-weight:bold; letter-spacing:1px; }
        .voucher .pkg { font-size:8px; color:#333; margin-top:4px; border-top:1px solid #ddd; padding-top:3px; }
        .voucher .price { font-size:9px; font-weight:bold; }
    </style>
</head>
<body>
    <div class="grid">
        @foreach($vouchers as $v)
            <div class="voucher">
                <div class="title">WiFi Hotspot Sekolah</div>
                <div class="portal">{{ $portal }}</div>
                <div class="cred">User: <span class="code">{{ $v->username }}</span></div>
                <div class="cred">Pass: <span class="code">{{ $v->password }}</span></div>
                <div class="pkg">
                    {{ $v->package?->name ?: 'Paket' }}
                    @if($v->package?->session_timeout) &middot; {{ $v->package->session_timeout }} @endif<br>
                    <span class="price">Rp {{ number_format($v->price,0,',','.') }}</span>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
