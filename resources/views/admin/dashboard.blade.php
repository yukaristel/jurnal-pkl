@php
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Proyek;
use App\Models\Siswa;
use Carbon\Carbon;

$today = today();
$stats = [
    'sekolah'         => \App\Models\Sekolah::count(),
    'perusahaan'      => \App\Models\Perusahaan::count(),
    'guru'            => \App\Models\User::where('role','guru')->count(),
    'pembimbing'      => \App\Models\User::where('role','pembimbing')->count(),
    'siswa'           => Siswa::count(),
    'penempatan'      => \App\Models\PenempatanPkl::where('status','aktif')->count(),
];

// Absensi hari ini
$absensiHariIni = Absensi::where('tanggal', $today)->get();
$absensiChart = [
    'hadir'       => $absensiHariIni->whereIn('status', ['hadir','terlambat'])->count(),
    'izin'        => $absensiHariIni->where('status','izin')->count(),
    'sakit'       => $absensiHariIni->where('status','sakit')->count(),
    'belum_absen' => max(0, $stats['siswa'] - $absensiHariIni->count()),
];

// Trend absensi 14 hari terakhir (bar chart: x=tanggal, y=jumlah)
$absensiTrend = [];
for ($i = 13; $i >= 0; $i--) {
    $date = Carbon::today()->subDays($i);
    $rows = Absensi::whereDate('tanggal', $date)->get();
    $absensiTrend['labels'][] = $date->format('d M');
    $absensiTrend['hadir'][] = $rows->whereIn('status',['hadir','terlambat'])->count();
    $absensiTrend['izin'][]  = $rows->where('status','izin')->count();
    $absensiTrend['sakit'][] = $rows->where('status','sakit')->count();
}

// Trend jurnal 14 hari terakhir
$jurnalTrend = [];
for ($i = 13; $i >= 0; $i--) {
    $date = Carbon::today()->subDays($i);
    $rows = Jurnal::whereDate('tanggal', $date)->get();
    $jurnalTrend['labels'][] = $date->format('d M');
    $jurnalTrend['done'][] = $rows->where('status','done')->count();
    $jurnalTrend['on_progress'][] = $rows->where('status','on_progress')->count();
    $jurnalTrend['hold'][] = $rows->where('status','hold')->count();
}

// PKL info: siswa yang sedang & akan selesai
$sekarang = Carbon::today();
$akanSelesai = \App\Models\PenempatanPkl::where('status','aktif')
    ->whereBetween('tanggal_selesai', [$sekarang, $sekarang->copy()->addDays(7)])
    ->with('siswa.user')
    ->orderBy('tanggal_selesai')
    ->limit(5)
    ->get();

$sudahBerjalan = \App\Models\PenempatanPkl::where('status','aktif')
    ->whereDate('tanggal_mulai', '<=', $sekarang)
    ->whereDate('tanggal_selesai', '>=', $sekarang)
    ->count();

// Recent absensi
$recentAbsensi = Absensi::with(['siswa.user'])
    ->orderByDesc('jam_masuk')
    ->limit(5)
    ->get();

$recentJurnal = Jurnal::with(['siswa.user'])
    ->orderByDesc('id')
    ->limit(5)
    ->get();
@endphp

@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan sistem Jurnal PKL')

@section('content')
{{-- Stat Cards: 4 per row --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-primary shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-building stat-icon"></i>
                <div class="stat-label">Sekolah</div>
                <div class="stat-value">{{ $stats['sekolah'] }}</div>
                <div class="stat-sub">{{ $stats['guru'] }} guru</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-success shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-briefcase stat-icon"></i>
                <div class="stat-label">Perusahaan</div>
                <div class="stat-value">{{ $stats['perusahaan'] }}</div>
                <div class="stat-sub">{{ $stats['pembimbing'] }} pembimbing</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-warning shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-mortarboard stat-icon"></i>
                <div class="stat-label">Siswa</div>
                <div class="stat-value">{{ $stats['siswa'] }}</div>
                <div class="stat-sub">{{ $sudahBerjalan }} PKL aktif</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-info shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-calendar-check stat-icon"></i>
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-value">{{ $absensiChart['hadir'] }}</div>
                <div class="stat-sub">dari {{ $stats['siswa'] }} siswa</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row: 2 kolom (Doughnut + Bar trend 14 hari) --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pie-chart text-primary"></i> Absensi Hari Ini</span>
                <small class="text-muted">{{ now()->format('d M Y') }}</small>
            </div>
            <div class="card-body">
                <canvas id="chartAbsensi" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart text-success"></i> Tren Absensi 14 Hari</span>
                <small class="text-muted">harian</small>
            </div>
            <div class="card-body">
                <canvas id="chartAbsensiTrend" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-12">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart text-success"></i> Tren Jurnal 14 Hari</span>
                <small class="text-muted">status harian (Done / On Progress / Hold)</small>
            </div>
            <div class="card-body">
                <canvas id="chartJurnalTrend" style="max-height: 240px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- PKL Info + Recent --}}
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-range text-warning"></i> PKL Akan Selesai</span>
                <small class="text-muted">7 hari ke depan</small>
            </div>
            <div class="card-body p-0">
                @forelse($akanSelesai as $p)
                    @php
                        $sisaHari = (int) Carbon::parse($p->tanggal_selesai)->diffInDays($sekarang, true);
                        $color = $sisaHari <= 2 ? 'danger' : ($sisaHari <= 5 ? 'warning' : 'primary');
                    @endphp
                    <div class="activity-item">
                        <div class="avatar bg-grad-{{ $color }}">{{ strtoupper(substr($p->siswa->user->name ?? '?', 0, 1)) }}</div>
                        <div class="info">
                            <div class="name">{{ $p->siswa->user->name ?? '-' }}</div>
                            <div class="desc">
                                <i class="bi bi-building"></i> {{ $p->perusahaan->nama ?? '-' }}
                                <br>
                                <i class="bi bi-calendar3"></i> {{ Carbon::parse($p->tanggal_mulai)->format('d M Y') }} – {{ Carbon::parse($p->tanggal_selesai)->format('d M Y') }}
                            </div>
                        </div>
                        <div class="time">
                            <span class="badge bg-{{ $color }}">{{ $sisaHari }} hari</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted p-4">Tidak ada.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history text-primary"></i> Absensi Terbaru</span>
                <a href="{{ route('admin.laporan.kehadiran') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentAbsensi as $a)
                    @php
                        $color = ['hadir'=>'success','terlambat'=>'warning','izin'=>'primary','sakit'=>'info','alpa'=>'danger'][$a->status] ?? 'secondary';
                    @endphp
                    <div class="activity-item">
                        <div class="avatar bg-grad-{{ $color }}">{{ strtoupper(substr($a->siswa->user->name ?? '?', 0, 1)) }}</div>
                        <div class="info">
                            <div class="name">{{ $a->siswa->user->name ?? '-' }}</div>
                            <div class="desc">
                                <span class="badge bg-{{ $color }}">{{ ucfirst($a->status) }}</span>
                                masuk {{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') }}
                            </div>
                        </div>
                        <div class="time">{{ \Carbon\Carbon::parse($a->jam_masuk)->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-center text-muted p-4">Belum ada absensi.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text text-success"></i> Jurnal Terbaru</span>
                <a href="{{ route('admin.jurnal.index') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentJurnal as $j)
                    @php
                        $color = ['on_progress'=>'primary','done'=>'success','hold'=>'warning','reprogress'=>'secondary'][$j->status] ?? 'secondary';
                    @endphp
                    <div class="activity-item">
                        <div class="avatar bg-grad-{{ $color }}">{{ strtoupper(substr($j->siswa->user->name ?? '?', 0, 1)) }}</div>
                        <div class="info">
                            <div class="name">{{ $j->judul }}</div>
                            <div class="desc">
                                <span class="badge bg-{{ $color }}">{{ str_replace('_',' ',$j->status) }}</span>
                                {{ $j->siswa->user->name ?? '-' }}
                            </div>
                        </div>
                        <div class="time">{{ \Carbon\Carbon::parse($j->created_at)->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-center text-muted p-4">Belum ada jurnal.</p>
                @endforelse
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
        labels: ['Hadir/Terlambat', 'Izin', 'Sakit', 'Belum Absen'],
        datasets: [{
            data: [@json($absensiChart['hadir']), @json($absensiChart['izin']), @json($absensiChart['sakit']), @json($absensiChart['belum_absen'])],
            backgroundColor: ['#10b981', '#6366f1', '#06b6d4', '#e2e8f0'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8, boxWidth: 10 } } },
        cutout: '65%',
    }
});

new Chart(document.getElementById('chartAbsensiTrend'), {
    type: 'bar',
    data: {
        labels: @json($absensiTrend['labels']),
        datasets: [
            { label: 'Hadir/Terlambat', data: @json($absensiTrend['hadir']), backgroundColor: '#10b981', borderRadius: 4, stack: 'a' },
            { label: 'Izin', data: @json($absensiTrend['izin']), backgroundColor: '#6366f1', borderRadius: 4, stack: 'a' },
            { label: 'Sakit', data: @json($absensiTrend['sakit']), backgroundColor: '#06b6d4', borderRadius: 4, stack: 'a' },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8 } } },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
        },
    }
});

new Chart(document.getElementById('chartJurnalTrend'), {
    type: 'bar',
    data: {
        labels: @json($jurnalTrend['labels']),
        datasets: [
            { label: 'Done', data: @json($jurnalTrend['done']), backgroundColor: '#10b981', borderRadius: 4, stack: 'a' },
            { label: 'On Progress', data: @json($jurnalTrend['on_progress']), backgroundColor: '#6366f1', borderRadius: 4, stack: 'a' },
            { label: 'Hold', data: @json($jurnalTrend['hold']), backgroundColor: '#f59e0b', borderRadius: 4, stack: 'a' },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8 } } },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
        },
    }
});
</script>
@endpush