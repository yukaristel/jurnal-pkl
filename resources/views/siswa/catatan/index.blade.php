@extends('layouts.admin')
@section('title', 'Catatan & Pesan')
@section('page-title', 'Catatan & Pesan')

@section('content')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="m-0"><i class="bi bi-chat-dots"></i> Catatan Pembimbing</h5>
            </div>
            <div class="card-body p-0">
                @if($catatanJurnal->isEmpty())
                    <p class="text-center text-muted p-4">Belum ada catatan.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($catatanJurnal as $c)
                            @php $unread = !$c->dibaca_at; @endphp
                            <li class="list-group-item {{ $unread ? 'bg-light' : '' }}">
                                <a href="{{ route('siswa.catatan.show', $c) }}" class="text-decoration-none text-dark d-block">
                                    <div class="d-flex justify-content-between">
                                        <strong>
                                            @if($unread)<span class="badge bg-primary me-1">Baru</span>@endif
                                            {{ $c->jurnal->judul ?? '-' }}
                                        </strong>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y H:i') }}</small>
                                    </div>
                                    <small class="text-muted d-block">dari {{ $c->pengirim->name ?? 'Pembimbing' }}</small>
                                    <p class="m-0 mt-1 text-truncate">{{ $c->isi }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if($catatanJurnal->hasPages())
                <div class="card-footer">{{ $catatanJurnal->links() }}</div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="m-0"><i class="bi bi-envelope"></i> Pesan Guru</h5>
            </div>
            <div class="card-body p-0">
                @if($pesan->isEmpty())
                    <p class="text-center text-muted p-4">Belum ada pesan.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($pesan as $p)
                            @php $unread = !$p->dibaca_at; @endphp
                            <li class="list-group-item {{ $unread ? 'bg-light' : '' }}">
                                <a href="{{ route('siswa.catatan.show', $p) }}" class="text-decoration-none text-dark d-block">
                                    <div class="d-flex justify-content-between">
                                        <strong>
                                            @if($unread)<span class="badge bg-primary me-1">Baru</span>@endif
                                            {{ $p->pengirim->name ?? 'Guru' }}
                                        </strong>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y H:i') }}</small>
                                    </div>
                                    <p class="m-0 mt-1 text-truncate">{{ $p->isi }}</p>
                                </a>
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