@extends('layouts.admin')
@section('title', 'Detail Jurnal #' . $jurnal->id)
@section('page-title', 'Detail Jurnal #' . $jurnal->id)

@section('content')
@php $c = match($jurnal->status) { 'on_progress' => 'primary', 'done' => 'success', 'hold' => 'warning', default => 'secondary' }; @endphp
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="m-0">{{ $jurnal->judul }}</h5>
                <small class="text-muted">#{{ $jurnal->id }} · {{ $jurnal->siswa->user->name ?? '-' }} · {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }}</small>
            </div>
            <div>
                <span class="badge bg-{{ $c }}">{{ str_replace('_',' ', $jurnal->status) }}</span>
            </div>
        </div>
        <hr>
        <p style="white-space: pre-line">{{ $jurnal->deskripsi }}</p>
        @if($jurnal->kesulitan)
            <div class="alert alert-warning"><strong>Kesulitan:</strong><p class="m-0 mt-1" style="white-space: pre-line">{{ $jurnal->kesulitan }}</p></div>
        @endif

        @if($jurnal->unlocked_by)
            <div class="alert alert-warning">
                <i class="bi bi-unlock"></i> Dibuka kunci oleh <strong>{{ $jurnal->unlockedByUser->name ?? 'admin' }}</strong>
                pada {{ \Carbon\Carbon::parse($jurnal->unlocked_at)->format('d M Y H:i') }}.
            </div>
        @endif

        @if($jurnal->status === 'done' && !$jurnal->unlocked_by)
            <form method="POST" action="{{ route('admin.jurnal.unlock', $jurnal) }}" onsubmit="return confirm('Buka kunci jurnal #{{ $jurnal->id }}? Siswa akan bisa mengedit kembali.')">
                @csrf
                <button class="btn btn-warning"><i class="bi bi-unlock"></i> Buka Kunci Jurnal Ini</button>
            </form>
        @endif
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><h6 class="m-0">Riwayat Status</h6></div>
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            @forelse($jurnal->statusLog->sortBy('waktu') as $log)
                <li><small class="text-muted">{{ \Carbon\Carbon::parse($log->waktu)->format('d M Y H:i:s') }}</small> — {{ $log->dari_status ?? '-' }} → <strong>{{ $log->ke_status }}</strong> oleh {{ $log->user->name ?? '?' }}</li>
            @empty
                <li class="text-muted">Tidak ada log.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection