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
                <h4 class="m-0">{{ $proyek->judul }}</h4>
                <small class="text-muted">{{ \Carbon\Carbon::parse($proyek->tanggal_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($proyek->target_selesai)->format('d M Y') }}</small>
            </div>
            <span class="badge bg-{{ $colors[$proyek->status] ?? 'secondary' }} fs-6">{{ str_replace('_',' ', $proyek->status) }}</span>
        </div>
        @if($proyek->deskripsi)
            <hr><p style="white-space: pre-line">{{ $proyek->deskripsi }}</p>
        @endif
        @if($proyek->hasil)
            <div class="alert alert-success"><strong>Hasil:</strong><br>{{ $proyek->hasil }}</div>
        @endif
        <hr>
        <strong>Progress: {{ $persen }}%</strong>
        <div class="progress mt-2" style="height: 12px;">
            <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
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
                        @if($pr->lampiran)
                            <small class="text-muted"><i class="bi bi-paperclip"></i> {{ basename($pr->lampiran) }}</small>
                        @endif
                    </div>
                @empty
                    <p class="text-muted text-center">Belum ada progress.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        @if($proyek->status !== 'selesai' && $proyek->status !== 'batal')
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h6 class="m-0">Tambah Progress</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('siswa.proyek.progress', $proyek) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Persen (%)</label>
                        <input type="number" name="persen" class="form-control" min="0" max="100" required value="{{ $persen }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Lampiran (opsional)</label>
                        <input type="file" name="lampiran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <button class="btn btn-primary btn-sm w-100">Simpan Progress</button>
                </form>
            </div>
        </div>
        @else
            <div class="alert alert-info">Proyek sudah {{ $proyek->status }}. Tidak bisa menambah progress.</div>
        @endif
    </div>
</div>
@endsection