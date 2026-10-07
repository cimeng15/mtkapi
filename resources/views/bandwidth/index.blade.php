@extends('layouts.admin')
@section('title', 'Bandwidth Monitoring')

@section('content')
@if($error)
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</div>
@endif

{{-- Kontrol --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
            <label class="form-check-label small" for="autoRefresh">Auto-refresh</label>
        </div>
        <select id="interval" class="form-select form-select-sm" style="width:auto">
            <option value="3">3 detik</option>
            <option value="5" selected>5 detik</option>
            <option value="10">10 detik</option>
            <option value="15">15 detik</option>
        </select>
    </div>
    <div>
        <span id="lastUpdate" class="text-muted small"></span>
        <span id="statusDot" class="ms-2" title="Status polling"></span>
    </div>
</div>

{{-- Stat ringkas --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat is-live">
            <div class="stat-label"><span class="live-dot"></span> Online Sekarang</div>
            <div class="stat-value" id="statOnline">—</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label"><i class="bi bi-cpu"></i> CPU Load</div>
            <div class="stat-value" id="statCpu">—</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label"><i class="bi bi-download"></i> Total Download</div>
            <div class="stat-value" id="statRx">—</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label"><i class="bi bi-upload"></i> Total Upload</div>
            <div class="stat-value" id="statTx">—</div>
        </div>
    </div>
</div>

{{-- Grafik bandwidth --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2"></i>Traffic Real-time</span>
                <select id="ifSelect" class="form-select form-select-sm" style="width:auto">
                    <option value="__all__">Semua Interface</option>
                    @foreach($interfaces as $iface)
                        <option value="{{ $iface['name'] }}">{{ $iface['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-body">
                <canvas id="trafficChart" height="260"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2"></i>Interface</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Nama</th><th class="text-end">RX</th><th class="text-end">TX</th></tr></thead>
                        <tbody id="ifaceTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top users --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Top Users — Bandwidth</span>
        <span class="badge bg-primary" id="userCount">0</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>#</th><th>User</th><th>IP</th><th>Uptime</th>
                <th class="text-end">Download</th><th class="text-end">Upload</th><th class="text-end">Total</th>
            </tr></thead>
            <tbody id="usersTable"></tbody>
        </table>
    </div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
(function(){
    const POLL_URL = @json(route('bandwidth.poll'));
    const MAX_POINTS = 60;

    // Helpers
    function fmtBytes(b){
        if(b===0) return '0 B';
        const u=['B','KB','MB','GB','TB']; let i=0; b=Math.abs(b);
        while(b>=1024 && i<4){b/=1024;i++;}
        return b.toFixed(i>0?2:0)+' '+u[i];
    }
    function fmtRate(b){
        if(b===0) return '0 bps';
        b*=8; // bytes→bits
        const u=['bps','Kbps','Mbps','Gbps']; let i=0;
        while(b>=1000 && i<3){b/=1000;i++;}
        return b.toFixed(i>0?2:0)+' '+u[i];
    }

    // Chart setup
    const ctx = document.getElementById('trafficChart').getContext('2d');
    const labels = [];
    const rxData = [];
    const txData = [];

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Download (RX)',
                    data: rxData,
                    borderColor: '#2E9E76',
                    backgroundColor: 'rgba(46,158,118,.08)',
                    fill: true,
                    tension: .3,
                    pointRadius: 0,
                    borderWidth: 2,
                },
                {
                    label: 'Upload (TX)',
                    data: txData,
                    borderColor: '#0E6E64',
                    backgroundColor: 'rgba(14,110,100,.06)',
                    fill: true,
                    tension: .3,
                    pointRadius: 0,
                    borderWidth: 2,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 300 },
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { display: true, grid: { display: false } },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.04)' },
                    ticks: { callback: v => fmtRate(v) }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: { label: ctx => ctx.dataset.label + ': ' + fmtRate(ctx.parsed.y) }
                },
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
            }
        }
    });

    // State untuk menghitung rate (delta bytes / delta time)
    let prevData = null;
    let prevTime = null;

    function getInterval(){ return parseInt(document.getElementById('interval').value) * 1000; }

    async function poll(){
        const dot = document.getElementById('statusDot');
        dot.innerHTML = '<span class="live-dot"></span>';
        try {
            const res = await fetch(POLL_URL);
            if(!res.ok) throw new Error(res.statusText);
            const d = await res.json();
            if(d.error){ dot.innerHTML = '<i class="bi bi-exclamation-circle text-danger"></i>'; return; }

            const selectedIf = document.getElementById('ifSelect').value;
            const now = Date.now();

            // Hitung total rx/tx untuk interface yang dipilih
            let totalRx = 0, totalTx = 0;
            (d.interfaces||[]).forEach(i => {
                if(selectedIf === '__all__' || i.name === selectedIf){
                    totalRx += i.rx;
                    totalTx += i.tx;
                }
            });

            // Hitung rate (bytes per detik) dari delta
            let rxRate = 0, txRate = 0;
            if(prevData !== null && prevTime !== null){
                const dt = (now - prevTime) / 1000; // detik
                if(dt > 0){
                    let prevRx = 0, prevTx = 0;
                    (prevData.interfaces||[]).forEach(i => {
                        if(selectedIf === '__all__' || i.name === selectedIf){
                            prevRx += i.rx;
                            prevTx += i.tx;
                        }
                    });
                    rxRate = Math.max(0, (totalRx - prevRx) / dt);
                    txRate = Math.max(0, (totalTx - prevTx) / dt);
                }
            }
            prevData = d;
            prevTime = now;

            // Update chart
            labels.push(d.time);
            rxData.push(rxRate);
            txData.push(txRate);
            if(labels.length > MAX_POINTS){
                labels.shift(); rxData.shift(); txData.shift();
            }
            chart.update('none');

            // Stats
            document.getElementById('statOnline').textContent = (d.users||[]).length;
            document.getElementById('statCpu').textContent = d.cpu + '%';
            document.getElementById('statRx').textContent = fmtBytes(totalRx);
            document.getElementById('statTx').textContent = fmtBytes(totalTx);
            document.getElementById('lastUpdate').textContent = 'Terakhir: ' + d.time;

            // Interface table
            const ifBody = document.getElementById('ifaceTable');
            ifBody.innerHTML = (d.interfaces||[]).map(i =>
                `<tr class="${i.running?'':'text-muted'}">
                    <td><i class="bi bi-${i.running?'check-circle text-success':'x-circle text-danger'} me-1"></i>${i.name}</td>
                    <td class="text-end"><small>${fmtBytes(i.rx)}</small></td>
                    <td class="text-end"><small>${fmtBytes(i.tx)}</small></td>
                </tr>`
            ).join('');

            // Users table
            const uBody = document.getElementById('usersTable');
            document.getElementById('userCount').textContent = (d.users||[]).length;
            uBody.innerHTML = (d.users||[]).map((u,idx) =>
                `<tr>
                    <td class="text-muted">${idx+1}</td>
                    <td class="fw-semibold"><i class="bi bi-person-fill text-success me-1"></i>${u.user}</td>
                    <td><code>${u.address}</code></td>
                    <td><small>${u.uptime}</small></td>
                    <td class="text-end">${fmtBytes(u.rx)}</td>
                    <td class="text-end">${fmtBytes(u.tx)}</td>
                    <td class="text-end fw-semibold">${fmtBytes(u.rx+u.tx)}</td>
                </tr>`
            ).join('') || '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengguna online.</td></tr>';

            dot.innerHTML = '<i class="bi bi-check-circle text-success"></i>';
        } catch(e){
            dot.innerHTML = '<i class="bi bi-exclamation-circle text-danger" title="'+e.message+'"></i>';
        }
    }

    // Polling loop
    let timer;
    function startPolling(){
        clearInterval(timer);
        poll();
        timer = setInterval(() => {
            if(document.getElementById('autoRefresh').checked) poll();
        }, getInterval());
    }

    document.getElementById('interval').addEventListener('change', startPolling);
    document.getElementById('ifSelect').addEventListener('change', () => {
        // Reset chart data saat ganti interface
        labels.length = 0; rxData.length = 0; txData.length = 0;
        prevData = null; prevTime = null;
        chart.update();
    });

    startPolling();
})();
</script>
@endpush
