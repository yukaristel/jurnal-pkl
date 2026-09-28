@php
use App\Models\Absensi;
use App\Models\Jurnal;
use Carbon\Carbon;

$siswa = auth()->user()->siswa;
$today = today();
$absensiHariIni = $siswa?->absensi()->where('tanggal', $today)->first();

// PKL info (untuk chart jurnal pakai tanggal mulai - selesai)
$penempatan = $siswa?->penempatanAktif;
$tglMulaiPKL = $penempatan ? Carbon::parse($penempatan->tanggal_mulai) : null;
$tglSelesaiPKL = $penempatan ? Carbon::parse($penempatan->tanggal_selesai) : null;
$today = Carbon::today();
$totalHariPKL = ($tglMulaiPKL && $tglSelesaiPKL) ? $tglMulaiPKL->diffInDays($tglSelesaiPKL, true) : 0;
$sudahBerjalan = ($tglMulaiPKL) ? max(0, $today->diffInDays($tglMulaiPKL, true)) : 0;
$sisaHari = ($tglSelesaiPKL) ? max(0, $tglSelesaiPKL->diffInDays($today, true)) : 0;
$progressPKL = $totalHariPKL > 0 ? min(100, round($sudahBerjalan / $totalHariPKL * 100)) : 0;

// Jurnal: tampilkan per hari dari tanggal mulai PKL sampai tanggal selesai (full range)
$jurnalTrend = ['labels' => [], 'done' => [], 'on_progress' => [], 'hold' => [], 'todayIndex' => null];
if ($tglMulaiPKL && $tglSelesaiPKL) {
    $current = $tglMulaiPKL->copy();
    while ($current->lte($tglSelesaiPKL)) {
        $rows = Jurnal::where('siswa_id', $siswa->id)->whereDate('tanggal', $current)->get();
        $jurnalTrend['labels'][]       = ''; // label tidak ditampilkan
        $jurnalTrend['done'][]         = $rows->where('status','done')->count();
        $jurnalTrend['on_progress'][]  = $rows->where('status','on_progress')->count();
        $jurnalTrend['hold'][]         = $rows->where('status','hold')->count();
        // Index hari ini untuk garis vertikal merah
        if ($jurnalTrend['todayIndex'] === null && $current->isSameDay($today)) {
            $jurnalTrend['todayIndex'] = count($jurnalTrend['labels']) - 1;
        }
        $current->addDay();
    }
}

// Absensi: pisahkan hadir dan terlambat (range tanggal PKL penuh)
// Terlambat = status 'terlambat' ATAU (status 'hadir' & jam_masuk melewati batas_terlambat hari itu)
$absensiTrend = ['labels' => [], 'hadir' => [], 'terlambat' => [], 'izin' => [], 'sakit' => [], 'todayIndex' => null];
if ($tglMulaiPKL && $tglSelesaiPKL && $penempatan) {
    $current = $tglMulaiPKL->copy();
    $dayCarbon = $current->dayOfWeekIso; // 1=Senin, 7=Minggu
    $pengaturan = \App\Models\PengaturanAbsensi::where('perusahaan_id', $penempatan->perusahaan_id)
        ->where('hari', $dayCarbon)
        ->first();
    $batasTerlambat = $pengaturan ? substr($pengaturan->batas_terlambat, 0, 5) : '08:30'; // HH:MM

    while ($current->lte($tglSelesaiPKL)) {
        $absen = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $current)
            ->first();

        $h = $tl = $i = $s = 0;
        if ($absen) {
            $status = $absen->status;
            $jam = $absen->jam_masuk ? Carbon::parse($absen->jam_masuk)->format('H:i') : '';
            if ($status === 'izin') $i = 1;
            elseif ($status === 'sakit') $s = 1;
            elseif ($status === 'terlambat' || ($status === 'hadir' && $jam && $jam > $batasTerlambat)) $tl = 1;
            elseif ($status === 'hadir') $h = 1;
        }

        $absensiTrend['labels'][]    = ''; // tidak ditampilkan
        $absensiTrend['hadir'][]      = $h;
        $absensiTrend['terlambat'][]  = $tl;
        $absensiTrend['izin'][]       = $i;
        $absensiTrend['sakit'][]      = $s;
        if ($absensiTrend['todayIndex'] === null && $current->isSameDay($today)) {
            $absensiTrend['todayIndex'] = count($absensiTrend['labels']) - 1;
        }
        $current->addDay();
        $dayCarbon = $dayCarbon >= 7 ? 1 : $dayCarbon + 1;
        $pengaturan = \App\Models\PengaturanAbsensi::where('perusahaan_id', $penempatan->perusahaan_id)
            ->where('hari', $dayCarbon)
            ->first();
        $batasTerlambat = $pengaturan ? substr($pengaturan->batas_terlambat, 0, 5) : '08:30';
    }
}

$proyekBerjalan = $siswa ? \App\Models\Proyek::where('siswa_id', $siswa->id)->where('status','berjalan')->count() : 0;
$proyekSelesai = $siswa ? \App\Models\Proyek::where('siswa_id', $siswa->id)->where('status','selesai')->count() : 0;

$jurnalHariIni = $siswa?->jurnal()->where('tanggal', $today)->get() ?? collect();
$waktuMasuk = $absensiHariIni ? Carbon::parse($absensiHariIni->jam_masuk) : null;
$waktuPulang = $absensiHariIni && $absensiHariIni->jam_pulang ? Carbon::parse($absensiHariIni->jam_pulang) : null;

$perusahaan = $penempatan?->perusahaan->nama ?? '-';
$pembimbing = $penempatan?->pembimbing->user->name ?? '-';
$guru = $penempatan?->guru->user->name ?? '-';
$tglMulai = $penempatan ? Carbon::parse($penempatan->tanggal_mulai)->format('d M Y') : '-';
$tglSelesai = $penempatan ? Carbon::parse($penempatan->tanggal_selesai)->format('d M Y') : '-';

// Total absensi per status (untuk doughnut)
$absensiTotals = [
    'hadir'     => array_sum($absensiTrend['hadir'] ?? []),
    'terlambat' => array_sum($absensiTrend['terlambat'] ?? []),
    'izin'      => array_sum($absensiTrend['izin'] ?? []),
    'sakit'     => array_sum($absensiTrend['sakit'] ?? []),
];
@endphp

@extends('layouts.admin')
@section('title', 'Dashboard Siswa')
@section('page-title', 'Dashboard Siswa')
@section('page-subtitle', $siswa->user->name ?? '-')

@section('content')
{{-- 4 Stat Cards dalam 1 baris --}}
<div class="row g-2 mb-3">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-grad-{{ $absensiHariIni ? ($absensiHariIni->jam_pulang ? 'success' : ($absensiHariIni->status === 'terlambat' ? 'warning' : 'primary')) : 'danger' }} shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-{{ $absensiHariIni ? 'check-circle' : 'exclamation-circle' }} stat-icon"></i>
                <div class="stat-label">Status Hari Ini</div>
                <div class="stat-value" style="font-size: 1.3rem;">
                    @if($absensiHariIni)
                        {{ ucfirst($absensiHariIni->status) }}
                    @else
                        Belum Absen
                    @endif
                </div>
                <div class="stat-sub">
                    @if($absensiHariIni && $waktuMasuk)
                        {{ $waktuMasuk->format('H:i') }}
                        @if($waktuPulang) – {{ $waktuPulang->format('H:i') }} @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-grad-purple shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-journal-text stat-icon"></i>
                <div class="stat-label">Jurnal Hari Ini</div>
                <div class="stat-value">{{ $jurnalHariIni->count() }}</div>
                <div class="stat-sub">{{ $jurnalHariIni->where('status','done')->count() }} selesai</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-grad-teal shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-kanban stat-icon"></i>
                <div class="stat-label">Proyek</div>
                <div class="stat-value">{{ $proyekBerjalan }}</div>
                <div class="stat-sub">berjalan · {{ $proyekSelesai }} selesai</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-grad-success shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-calendar-week stat-icon"></i>
                <div class="stat-label">Sisa PKL</div>
                <div class="stat-value">{{ $sisaHari }}</div>
                <div class="stat-sub">{{ $tglMulai }} → {{ $tglSelesai }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Info PKL ringkas (1 baris) --}}
@if($penempatan)
<div class="card card-flat mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center text-sm">
            <div class="col-md-6">
                <i class="bi bi-building text-primary"></i>
                <strong>{{ $perusahaan }}</strong>
                <span class="text-muted ms-2">
                    <i class="bi bi-person-workspace"></i> {{ $pembimbing }}
                    · <i class="bi bi-person-badge"></i> {{ $guru }}
                </span>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <small class="text-muted">
                        Hari <strong>{{ $sudahBerjalan }}</strong> dari <strong>{{ $totalHariPKL }}</strong>
                        @if($sisaHari > 0)
                            · tersisa <strong class="text-primary">{{ $sisaHari }} hari</strong>
                        @else
                            · <strong class="text-success">sudah selesai</strong>
                        @endif
                    </small>
                    <div class="progress" style="height: 8px; width: 200px;">
                        <div class="progress-bar bg-grad-primary" style="width: {{ $progressPKL }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Action Button conditional --}}
@if(!$absensiHariIni)
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card stat-card bg-grad-info shadow-sm">
            <div class="card-body text-center py-3">
                <h5 class="mb-2 text-white"><i class="bi bi-qr-code-scan"></i> Anda belum absen hari ini</h5>
                <a href="{{ route('siswa.absen.scan') }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-qr-code-scan"></i> Scan Barcode
                </a>
                <a href="{{ route('siswa.absen.izin') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-file-medical"></i> Ajukan Izin/Sakit
                </a>
            </div>
        </div>
    </div>
</div>
@elseif(!$absensiHariIni->jam_pulang && in_array($absensiHariIni->status, ['hadir','terlambat']))
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card stat-card bg-grad-danger shadow-sm">
            <div class="card-body text-center py-3">
                <h5 class="mb-2 text-white"><i class="bi bi-clock"></i> Sudah waktunya pulang?</h5>
                <button type="button" class="btn btn-light btn-sm" id="btn-pulang">
                    <i class="bi bi-box-arrow-right"></i> Absen Pulang
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Charts Row: Absensi (col-4 doughnut) + Jurnal (col-8 line) --}}
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-flat h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart text-primary"></i> Absensi
            </div>
            <div class="card-body">
                <canvas id="chartAbsensi" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-flat h-100">
            <div class="card-header">
                <i class="bi bi-graph-up text-success"></i> Jurnal PKL
            </div>
            <div class="card-body">
                <canvas id="chartJurnal" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('chartAbsensi'), {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit'],
        datasets: [{
            data: [@json($absensiTotals['hadir']), @json($absensiTotals['terlambat']), @json($absensiTotals['izin']), @json($absensiTotals['sakit'])],
            backgroundColor: ['#10b981', '#f59e0b', '#6366f1', '#06b6d4'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8, boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const total = ctx.dataset.data.reduce((a,b)=>a+b, 0);
                        const v = ctx.parsed;
                        const pct = total > 0 ? Math.round(v / total * 100) : 0;
                        return ctx.label + ': ' + v + ' hari (' + pct + '%)';
                    }
                }
            }
        },
        cutout: '60%',
    }
});

new Chart(document.getElementById('chartJurnal'), {
    type: 'line',
    data: {
        labels: @json($jurnalTrend['labels']),
        datasets: [
            { label: 'Done', data: @json($jurnalTrend['done']), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: 0, pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
            { label: 'On Progress', data: @json($jurnalTrend['on_progress']), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.12)', fill: true, tension: 0, pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
            { label: 'Hold', data: @json($jurnalTrend['hold']), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.12)', fill: true, tension: 0, pointRadius: 0, pointHoverRadius: 4, borderWidth: 2 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8 } },
            tooltip: { mode: 'index', intersect: false },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { display: false },
            },
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
        },
        interaction: { mode: 'index', intersect: false },
    },
    plugins: [{
        id: 'todayLine',
        afterDraw(chart) {
            const idx = @json($jurnalTrend['todayIndex']);
            if (idx === null || idx < 0) return;
            const xScale = chart.scales.x;
            const yScale = chart.scales.y;
            if (!xScale || !yScale) return;
            const x = xScale.getPixelForValue(idx);
            const ctx = chart.ctx;
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(x, yScale.top);
            ctx.lineTo(x, yScale.bottom);
            ctx.lineWidth = 1.5;
            ctx.strokeStyle = 'rgba(220, 38, 38, 0.85)';
            ctx.setLineDash([4, 4]);
            ctx.stroke();
            // Label "Hari Ini" di atas garis
            ctx.setLineDash([]);
            ctx.fillStyle = 'rgba(220, 38, 38, 0.95)';
            ctx.font = '600 10px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Hari Ini', x, yScale.top + 12);
            ctx.restore();
        }
    }]
});

const btnPulang = document.getElementById('btn-pulang');
if (btnPulang) {
    btnPulang.addEventListener('click', async () => {
        if (!confirm('Konfirmasi pulang?')) return;
        const res = await fetch("{{ route('siswa.absen.pulang') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
        });
        const data = await res.json();
        alert(data.message);
        if (data.ok) window.location.reload();
    });
}
</script>
@endpush