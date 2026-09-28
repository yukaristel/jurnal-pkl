@extends('layouts.admin')
@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('pembimbing.laporan.kehadiran') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check fs-1 text-primary"></i>
                    <h5 class="mt-2">Laporan Kehadiran</h5>
                    <small class="text-muted">Rekap absensi siswa (tanggal, jam, status)</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('pembimbing.laporan.jurnal') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-journal-text fs-1 text-success"></i>
                    <h5 class="mt-2">Laporan Jurnal</h5>
                    <small class="text-muted">Rekap jurnal kegiatan + catatan pembimbing</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('pembimbing.laporan.proyek') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-kanban fs-1 text-warning"></i>
                    <h5 class="mt-2">Laporan Proyek</h5>
                    <small class="text-muted">Rekap proyek + progress</small>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection