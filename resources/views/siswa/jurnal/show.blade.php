@extends('layouts.admin')
@section('title', 'Detail Jurnal')
@section('page-title', 'Detail Jurnal')

@section('content')
@php
    $color = match($jurnal->status) {
        'on_progress' => 'primary',
        'done' => 'success',
        'hold' => 'warning',
        'reprogress' => 'secondary',
    };
    $isLocked = $jurnal->status === 'done' && $jurnal->unlocked_by === null;
@endphp

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="m-0">{{ $jurnal->judul }}</h4>
                <small class="text-muted">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }} · {{ \Carbon\Carbon::parse($jurnal->created_at)->format('H:i') }}</small>
            </div>
            <span class="badge bg-{{ $color }} fs-6">{{ str_replace('_',' ', $jurnal->status) }}</span>
        </div>

        <hr>

        <p style="white-space: pre-line">{{ $jurnal->deskripsi }}</p>

        @if($jurnal->kesulitan)
            <div class="alert alert-warning">
                <strong>Kesulitan:</strong>
                <p class="m-0 mt-1" style="white-space: pre-line">{{ $jurnal->kesulitan }}</p>
            </div>
        @endif

        @if($isLocked)
            <div class="alert alert-info">
                <i class="bi bi-lock"></i> Jurnal ini dikunci (status: done). Hanya superadmin yang bisa membuka kunci.
            </div>
        @endif

        @if($jurnal->unlocked_by && $jurnal->unlocked_at)
            <div class="alert alert-warning">
                <i class="bi bi-unlock"></i> Dibuka kunci oleh admin pada {{ \Carbon\Carbon::parse($jurnal->unlocked_at)->format('d M Y H:i') }}.
            </div>
        @endif

        <div class="d-flex gap-2 mt-3 flex-wrap">
            @if($jurnal->status === 'on_progress')
                <form method="POST" action="{{ route('siswa.jurnal.done', $jurnal) }}" class="m-0">
                    @csrf
                    <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle"></i> Tandai Selesai</button>
                </form>
                <form method="POST" action="{{ route('siswa.jurnal.hold', $jurnal) }}" class="m-0">
                    @csrf
                    <button class="btn btn-warning btn-sm"><i class="bi bi-pause-circle"></i> Hold</button>
                </form>
            @endif

            @if($jurnal->status === 'hold')
                <form method="POST" action="{{ route('siswa.jurnal.reprogress', $jurnal) }}" class="m-0">
                    @csrf
                    <button class="btn btn-primary btn-sm"><i class="bi bi-arrow-clockwise"></i> Buka Kembali (Reprogress)</button>
                </form>
            @endif

            @if(!$isLocked)
                <a href="{{ route('siswa.jurnal.edit', $jurnal) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            @endif
        </div>
    </div>
</div>

@if($jurnal->children->count())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="m-0">Riwayat Pengerjaan</h6>
        </div>
        <div class="card-body">
            @foreach($jurnal->children as $child)
                <div class="border-start ps-3 mb-2">
                    <a href="{{ route('siswa.jurnal.show', $child) }}">{{ $child->judul }}</a>
                    <span class="badge bg-{{ match($child->status) { 'on_progress' => 'primary', 'done' => 'success', 'hold' => 'warning', default => 'secondary' } }}">{{ str_replace('_',' ', $child->status) }}</span>
                    <small class="text-muted d-block">{{ \Carbon\Carbon::parse($child->created_at)->format('d M Y H:i') }}</small>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($jurnal->catatan->count())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h6 class="m-0">Catatan Pembimbing</h6>
        </div>
        <div class="card-body">
            @foreach($jurnal->catatan as $c)
                <div class="border-start ps-3 mb-2">
                    <small class="text-muted">{{ $c->pengirim->name ?? '?' }} · {{ \Carbon\Carbon::parse($c->created_at)->format('d M Y H:i') }}</small>
                    <p class="m-0 mt-1" style="white-space: pre-line">{{ $c->isi }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h6 class="m-0">Riwayat Status</h6>
    </div>
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            @foreach($jurnal->statusLog->sortBy('waktu') as $log)
                <li>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->waktu)->format('d M Y H:i:s') }}</small>
                    — {{ $log->dari_status ?? '-' }} → <strong>{{ $log->ke_status }}</strong>
                    oleh {{ $log->user->name ?? '?' }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection