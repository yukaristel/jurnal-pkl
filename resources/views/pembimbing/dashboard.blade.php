@php
use App\Models\Absensi;
use App\Models\Jurnal;
use Carbon\Carbon;

$pembimbing = auth()->user()->pembimbing;
$siswaIds = \App\Models\PenempatanPkl::where('pembimbing_id', $pembimbing->id)
    ->where('status','aktif')
    ->pluck('siswa_id');

$today = today();
$totalSiswa = $siswaIds->count();
$absensiHariIni = Absensi::whereIn('siswa_id', $siswaIds)->where('tanggal', $today)->get();
$hadir = $absensiHariIni->whereIn('status', ['hadir','terlambat'])->count();
$terlambat = $absensiHariIni->where('status','terlambat')->count();

$absensiTrend = [];
for ($i = 13; $i >= 0; $i--) {
    $date = Carbon::today()->subDays($i);
    $rows = Absensi::whereIn('siswa_id', $siswaIds)->whereDate('tanggal', $date)->get();
    $absensiTrend['labels'][] = $date->format('d M');
    $absensiTrend['hadir'][] = $rows->whereIn('status',['hadir','terlambat'])->count();
    $absensiTrend['izin'][]  = $rows->where('status','izin')->count();
    $absensiTrend['sakit'][] = $rows->where('status','sakit')->count();
}

$jurnalTrend = [];
for ($i = 13; $i >= 0; $i--) {
    $date = Carbon::today()->subDays($i);
    $rows = Jurnal::whereIn('siswa_id', $siswaIds)->whereDate('tanggal', $date)->get();
    $jurnalTrend['labels'][] = $date->format('d M');
    $jurnalTrend['done'][] = $rows->where('status','done')->count();
    $jurnalTrend['on_progress'][] = $rows->where('status','on_progress')->count();
    $jurnalTrend['hold'][] = $rows->where('status','hold')->count();
}

$jurnalHariIni = Jurnal::whereIn('siswa_id', $siswaIds)->where('tanggal', $today)->get();
$proyekBerjalan = \App\Models\Proyek::whereIn('siswa_id', $siswaIds)->where('status','berjalan')->count();

$akanSelesai = \App\Models\PenempatanPkl::where('pembimbing_id', $pembimbing->id)
    ->where('status','aktif')
    ->whereBetween('tanggal_selesai', [$today, Carbon::today()->addDays(7)])
    ->with('siswa.user')
    ->orderBy('tanggal_selesai')
    ->limit(5)
    ->get();
@endphp

@extends('layouts.admin')
@section('title', 'Dashboard Pembimbing')
@section('page-title', 'Dashboard Pembimbing')
@section('page-subtitle', $pembimbing->perusahaan->nama ?? '-')

@section('content')
{{-- QR Quick Action --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card stat-card bg-grad-primary shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 text-white"><i class="bi bi-qr-code-scan"></i> QR Absensi</h6>
                    <small class="text-white-50">Tampilkan QR Code untuk siswa scan</small>
                </div>
                <a href="{{ route('pembimbing.absensi.qr') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-qr-code"></i> Buka QR Code
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards: 4 per row --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-success shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-mortarboard stat-icon"></i>
                <div class="stat-label">Siswa Bimbingan</div>
                <div class="stat-value">{{ $totalSiswa }}</div>
                <div class="stat-sub">{{ $pembimbing->perusahaan->nama ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-info shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-check-circle stat-icon"></i>
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-value">{{ $hadir }}</div>
                <div class="stat-sub">
                    {{ $totalSiswa > 0 ? round($hadir / $totalSiswa * 100) : 0 }}% siswa
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-warning shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-journal-text stat-icon"></i>
                <div class="stat-label">Jurnal Hari Ini</div>
                <div class="stat-value">{{ $jurnalHariIni->count() }}</div>
                <div class="stat-sub">
                    {{ $jurnalHariIni->where('status','done')->count() }} selesai
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-grad-purple shadow-sm h-100">
            <div class="card-body">
                <i class="bi bi-kanban stat-icon"></i>
                <div class="stat-label">Proyek Berjalan</div>
                <div class="stat-value">{{ $proyekBerjalan }}</div>
                <div class="stat-sub">aktif saat ini</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart text-primary"></i> Tren Absensi 14 Hari</span>
                <small class="text-muted">harian</small>
            </div>
            <div class="card-body">
                <canvas id="chartAbsensi" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-flat h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart text-success"></i> Tren Jurnal 14 Hari</span>
                <small class="text-muted">status harian</small>
            </div>
            <div class="card-body">
                <canvas id="chartJurnal" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- PKL Akan Selesai --}}
<div class="row g-3">
    <div class="col-lg-12">
        <div class="card card-flat">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-range text-warning"></i> PKL Akan Selesai (7 hari ke depan)</span>
            </div>
            <div class="card-body p-0">
                @forelse($akanSelesai as $p)
                    @php
                        $sisaHari = (int) Carbon::parse($p->tanggal_selesai)->diffInDays($today, true);
                        $color = $sisaHari <= 2 ? 'danger' : ($sisaHari <= 5 ? 'warning' : 'primary');
                    @endphp
                    <div class="activity-item">
                        <div class="avatar bg-grad-{{ $color }}">{{ strtoupper(substr($p->siswa->user->name ?? '?', 0, 1)) }}</div>
                        <div class="info">
                            <div class="name">{{ $p->siswa->user->name ?? '-' }} ({{ $p->siswa->nis ?? '-' }})</div>
                            <div class="desc">
                                {{ Carbon::parse($p->tanggal_mulai)->format('d M Y') }} – {{ Carbon::parse($p->tanggal_selesai)->format('d M Y') }}
                            </div>
                        </div>
                        <div class="time">
                            <span class="badge bg-{{ $color }}">{{ $sisaHari }} hari</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-muted p-4">Tidak ada PKL akan selesai dalam 7 hari ke depan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('chartAbsensi'), {
    type: 'bar',
    data: {
        labels: @json($absensiTrend['labels']),
        datasets: [
            { label: 'Hadir', data: @json($absensiTrend['hadir']), backgroundColor: '#10b981', borderRadius: 4, stack: 'a' },
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

new Chart(document.getElementById('chartJurnal'), {
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