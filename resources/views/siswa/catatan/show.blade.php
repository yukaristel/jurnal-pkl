@extends('layouts.admin')
@section('title', 'Detail Pesan')
@section('page-title', 'Detail')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="m-0">
                    @if($catatan->tipe === 'catatan_jurnal')
                        <i class="bi bi-chat-dots"></i> Catatan untuk: {{ $catatan->jurnal->judul ?? '-' }}
                    @else
                        <i class="bi bi-envelope"></i> Pesan dari {{ $catatan->pengirim->name ?? 'Guru' }}
                    @endif
                </h5>
                <small class="text-muted">{{ \Carbon\Carbon::parse($catatan->created_at)->format('d M Y H:i') }}</small>
            </div>
            @if($catatan->tipe === 'catatan_jurnal' && $catatan->jurnal)
                <a href="{{ route('siswa.jurnal.show', $catatan->jurnal) }}" class="btn btn-sm btn-outline-primary">Lihat Jurnal</a>
            @endif
        </div>

        <p style="white-space: pre-line">{{ $catatan->isi }}</p>

        <hr>
        <small class="text-muted">
            @if($catatan->dibaca_at)
                <i class="bi bi-check2-circle text-success"></i> Dibaca {{ \Carbon\Carbon::parse($catatan->dibaca_at)->diffForHumans() }}
            @endif
        </small>
    </div>
</div>
<a href="{{ route('siswa.catatan.index') }}" class="btn btn-secondary mt-3">Kembali</a>
@endsection