@extends('layouts.admin')
@section('title', 'Tambah Catatan')
@section('page-title', 'Catatan untuk Jurnal')

@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="m-0">{{ $jurnal->judul }}</h6>
        <small class="text-muted">{{ $jurnal->siswa->user->name ?? '-' }} · {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }}</small>
        <hr>
        <p style="white-space: pre-line">{{ $jurnal->deskripsi }}</p>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('pembimbing.catatan.store', $jurnal) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Isi Catatan</label>
                <textarea name="isi" class="form-control @error('isi') is-invalid @enderror" rows="6" required placeholder="Berikan evaluasi, tips, atau catatan untuk siswa..."></textarea>
                @error('isi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">Kirim Catatan</button>
            <a href="{{ route('pembimbing.jurnal.show', $jurnal) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection