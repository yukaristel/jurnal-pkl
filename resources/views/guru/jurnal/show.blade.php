@extends('layouts.admin')
@section('title', 'Detail Jurnal')
@section('page-title', 'Detail Jurnal Siswa')

@section('content')
@php $c = match($jurnal->status) { 'on_progress' => 'primary', 'done' => 'success', 'hold' => 'warning', default => 'secondary' }; @endphp
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5 class="m-0">{{ $jurnal->judul }}</h5>
        <small class="text-muted">{{ $jurnal->siswa->user->name ?? '-' }} · {{ $jurnal->siswa->sekolah->nama ?? '-' }} · {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y') }}</small>
        <span class="badge bg-{{ $c }} ms-2">{{ str_replace('_',' ', $jurnal->status) }}</span>
        <hr>
        <p style="white-space: pre-line">{{ $jurnal->deskripsi }}</p>
        @if($jurnal->kesulitan)
            <div class="alert alert-warning"><strong>Kesulitan:</strong><p class="m-0 mt-1" style="white-space: pre-line">{{ $jurnal->kesulitan }}</p></div>
        @endif
    </div>
</div>
@if($jurnal->catatan->count())
    <div class="card shadow-sm">
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