@extends('layouts.admin')
@section('title', 'Laporan Kehadiran')
@section('page-title', 'Laporan Kehadiran')

@section('content')
@include('laporan._filter', ['statusOptions' => ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa']])

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <strong>Ringkasan:</strong>
        Total {{ $summary['total'] }} · Hadir {{ $summary['hadir'] }} · Terlambat {{ $summary['terlambat'] }} · Izin {{ $summary['izin'] }} · Sakit {{ $summary['sakit'] }} · Alpa {{ $summary['alpa'] }}
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>#</th><th>Tanggal</th><th>Siswa</th><th>Perusahaan</th><th>Masuk</th><th>Pulang</th><th>Durasi</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody>
                @forelse($rows as $i => $r)
                    @php
                        $color = ['hadir'=>'success','terlambat'=>'warning','izin'=>'primary','sakit'=>'info','alpa'=>'danger'][$r->status] ?? 'secondary';
                        $durasi = '';
                        if ($r->jam_masuk && $r->jam_pulang) {
                            $d = \Carbon\Carbon::parse($r->jam_masuk)->diff(\Carbon\Carbon::parse($r->jam_pulang));
                            $durasi = "{$d->h}j {$d->i}m";
                        }
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                        <td>{{ $r->siswa->user->name ?? '-' }}<br><small class="text-muted">{{ $r->siswa->nis ?? '' }}</small></td>
                        <td>{{ $r->penempatan->perusahaan->nama ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->jam_masuk)->format('H:i:s') }}</td>
                        <td>{{ $r->jam_pulang ? \Carbon\Carbon::parse($r->jam_pulang)->format('H:i:s') : '-' }}</td>
                        <td>{{ $durasi ?: '-' }}</td>
                        <td><span class="badge bg-{{ $color }}">{{ ucfirst($r->status) }}</span></td>
                        <td>{{ $r->keterangan ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection