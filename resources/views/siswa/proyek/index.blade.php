@extends('layouts.admin')
@section('title', 'Proyek')
@section('page-title', 'Proyek Saya')

@section('content')
<div class="row g-3">
    @forelse($proyeks as $p)
        @php
            $last = $p->progress->last();
            $persen = $last?->persen ?? 0;
            $colors = ['belum_mulai' => 'secondary', 'berjalan' => 'primary', 'selesai' => 'success', 'batal' => 'danger'];
        @endphp
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="m-0">{{ $p->judul }}</h6>
                        <span class="badge bg-{{ $colors[$p->status] ?? 'secondary' }}">{{ str_replace('_',' ',$p->status) }}</span>
                    </div>
                    <small class="text-muted">Target: {{ \Carbon\Carbon::parse($p->target_selesai)->format('d M Y') }}</small>
                    @if($p->deskripsi)
                        <p class="mt-2 text-muted small">{{ \Illuminate\Support\Str::limit($p->deskripsi, 100) }}</p>
                    @endif
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                    </div>
                    <small>{{ $persen }}% selesai</small>
                    <div class="mt-2 text-end">
                        <a href="{{ route('siswa.proyek.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail & Tambah Progress</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info">Belum ada proyek.</div></div>
    @endforelse
</div>

<div class="mt-3">{{ $proyeks->links() }}</div>
@endsection