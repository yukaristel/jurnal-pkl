@extends('layouts.admin')
@section('title', 'Detail Proyek')
@section('page-title', 'Detail Proyek')

@section('content')
@php
    $colors = ['belum_mulai' => 'secondary', 'berjalan' => 'primary', 'selesai' => 'success', 'batal' => 'danger'];
    $last = $proyek->progress->last();
    $persen = $last?->persen ?? 0;
@endphp
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="m-0">#{{ $proyek->id }} · {{ $proyek->judul }}</h4>
                <small class="text-muted">{{ $proyek->siswa->user->name ?? '-' }} · {{ $proyek->siswa->sekolah->nama ?? '-' }} · {{ \Carbon\Carbon::parse($proyek->tanggal_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($proyek->target_selesai)->format('d M Y') }}</small>
            </div>
            <div>
                <span class="badge bg-{{ $colors[$proyek->status] ?? 'secondary' }} fs-6">{{ str_replace('_',' ', $proyek->status) }}</span>
                <form method="POST" action="{{ route('admin.proyek.destroy', $proyek) }}" class="d-inline" onsubmit="return confirm('Hapus proyek?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger ms-2"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
        <hr>
        <p style="white-space: pre-line">{{ $proyek->deskripsi ?? '(Tidak ada deskripsi)' }}</p>
        <strong>Progress: {{ $persen }}%</strong>
        <div class="progress mt-2" style="height: 12px;">
            <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
        </div>
        @if($proyek->hasil)
            <div class="alert alert-success mt-3"><strong>Hasil:</strong><br>{{ $proyek->hasil }}</div>
        @endif
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><h6 class="m-0">Riwayat Progress</h6></div>
    <div class="card-body">
        @forelse($proyek->progress->sortByDesc('created_at') as $pr)
            <div class="border-start ps-3 mb-3">
                <div class="d-flex justify-content-between">
                    <strong>{{ $pr->persen }}%</strong>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($pr->created_at)->format('d M Y H:i') }}</small>
                </div>
                <p class="m-0 mt-1" style="white-space: pre-line">{{ $pr->keterangan }}</p>
            </div>
        @empty
            <p class="text-muted text-center">Belum ada progress.</p>
        @endforelse
    </div>
</div>
@endsection