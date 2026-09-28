@extends('layouts.admin')
@section('title', 'Detail Jurnal Siswa')
@section('page-title', 'Detail Jurnal Siswa')

@section('content')
@php $c = match($jurnal->status) { 'on_progress' => 'primary', 'done' => 'success', 'hold' => 'warning', default => 'secondary' }; @endphp
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="m-0">{{ $jurnal->judul }}</h5>
                <small class="text-muted">{{ $jurnal->siswa->user->name ?? '-' }} ({{ $jurnal->siswa->nis ?? '-' }}) · {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }}</small>
            </div>
            <div>
                <span class="badge bg-{{ $c }}">{{ str_replace('_',' ', $jurnal->status) }}</span>
                <a href="{{ route('pembimbing.catatan.create', $jurnal) }}" class="btn btn-sm btn-primary ms-2"><i class="bi bi-chat-dots"></i> Beri Catatan</a>
            </div>
        </div>
        <hr>
        <p style="white-space: pre-line">{{ $jurnal->deskripsi }}</p>
        @if($jurnal->kesulitan)
            <div class="alert alert-warning"><strong>Kesulitan:</strong><p class="m-0 mt-1" style="white-space: pre-line">{{ $jurnal->kesulitan }}</p></div>
        @endif
    </div>
</div>

@if($jurnal->children->count())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><h6 class="m-0">Riwayat Pengerjaan</h6></div>
        <div class="card-body">
            @foreach($jurnal->children as $child)
                <div class="border-start ps-3 mb-2">
                    <a href="{{ route('pembimbing.jurnal.show', $child) }}">{{ $child->judul }}</a>
                    <span class="badge bg-{{ match($child->status) { 'on_progress' => 'primary', 'done' => 'success', 'hold' => 'warning', default => 'secondary' } }}">{{ str_replace('_',' ', $child->status) }}</span>
                    <small class="text-muted d-block">{{ \Carbon\Carbon::parse($child->created_at)->format('d M Y H:i') }}</small>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($jurnal->catatan->count())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><h6 class="m-0">Catatan Pembimbing</h6></div>
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
@endsection