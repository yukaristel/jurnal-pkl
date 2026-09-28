@extends('layouts.admin')
@section('title', 'Pesan Siswa')
@section('page-title', 'Kirim & Riwayat Pesan')

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="m-0">Kirim Pesan</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('guru.pesan.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kepada Siswa</label>
                        <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswa as $s)
                                <option value="{{ $s->id }}">{{ $s->nis }} — {{ $s->user->name }}</option>
                            @endforeach
                        </select>
                        @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Pesan</label>
                        <textarea name="isi" class="form-control @error('isi') is-invalid @enderror" rows="5" required placeholder="Tulis pesan motivasi, instruksi, atau informasi..."></textarea>
                        @error('isi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary">Kirim</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="m-0">Riwayat Pesan</h5></div>
            <div class="card-body p-0">
                @if($pesan->isEmpty())
                    <p class="text-center text-muted p-4">Belum ada pesan.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($pesan as $p)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $p->siswa->user->name ?? '-' }}</strong>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y H:i') }}</small>
                                </div>
                                <p class="m-0 mt-1 text-muted" style="white-space: pre-line">{{ \Illuminate\Support\Str::limit($p->isi, 200) }}</p>
                                @if($p->dibaca_at)
                                    <small class="text-success"><i class="bi bi-check2"></i> Dibaca {{ \Carbon\Carbon::parse($p->dibaca_at)->diffForHumans() }}</small>
                                @else
                                    <small class="text-warning"><i class="bi bi-envelope"></i> Belum dibaca</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if($pesan->hasPages())
                <div class="card-footer">{{ $pesan->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection