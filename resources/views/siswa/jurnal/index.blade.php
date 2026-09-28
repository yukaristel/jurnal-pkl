@extends('layouts.admin')
@section('title', 'Jurnal Hari Ini')
@section('page-title', 'Jurnal Hari Ini')

@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ \Carbon\Carbon::today()->format('d M Y') }}</strong>
            @if($absensiHariIni)
                <span class="badge bg-{{ $absensiHariIni->status === 'terlambat' ? 'warning' : 'success' }} ms-2">{{ ucfirst($absensiHariIni->status) }}</span>
            @endif
        </div>
        @if($canCreate && !$absensiHariIni?->jam_pulang)
            <a href="{{ route('siswa.jurnal.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Jurnal</a>
        @elseif($absensiHariIni?->jam_pulang)
            <span class="text-muted small">Sudah pulang</span>
        @else
            <span class="text-muted small">Belum absen hari ini</span>
        @endif
    </div>
</div>

@if($jurnals->isEmpty())
    <div class="alert alert-info">Belum ada jurnal hari ini.</div>
@else
    @foreach($jurnals as $j)
        @php
            $color = match($j->status) {
                'on_progress' => 'primary',
                'done' => 'success',
                'hold' => 'warning',
                'reprogress' => 'secondary',
            };
        @endphp
        <div class="card shadow-sm mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="m-0">{{ $j->judul }}</h6>
                        <p class="text-muted mb-1 mt-1">{{ \Illuminate\Support\Str::limit($j->deskripsi, 120) }}</p>
                        @if($j->kesulitan)
                            <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ \Illuminate\Support\Str::limit($j->kesulitan, 80) }}</small>
                        @endif
                        @if($j->children->count())
                            <small class="text-muted d-block"><i class="bi bi-arrow-return-right"></i> {{ $j->children->count() }} jurnal lanjutan</small>
                        @endif
                    </div>
                    <span class="badge bg-{{ $color }}">{{ str_replace('_',' ', $j->status) }}</span>
                </div>
                <div class="mt-2 d-flex justify-content-between align-items-center">
                    <small class="text-muted">{{ $j->catatan->count() }} catatan</small>
                    <a href="{{ route('siswa.jurnal.show', $j) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection