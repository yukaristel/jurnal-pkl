@extends('layouts.admin')
@section('title', 'Laporan Proyek')
@section('page-title', 'Laporan Proyek')

@section('content')
@include('laporan._filter', ['statusOptions' => ['belum_mulai'=>'Belum Mulai','berjalan'=>'Berjalan','selesai'=>'Selesai','batal'=>'Batal']])

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <strong>Ringkasan:</strong>
        Total {{ $summary['total'] }} · Belum Mulai {{ $summary['belum_mulai'] }} · Berjalan {{ $summary['berjalan'] }} · Selesai {{ $summary['selesai'] }} · Batal {{ $summary['batal'] }}
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>#</th><th>Siswa</th><th>Judul</th><th>Periode</th><th>Progress</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($rows as $i => $p)
                    @php
                        $color = ['belum_mulai'=>'secondary','berjalan'=>'primary','selesai'=>'success','batal'=>'danger'][$p->status] ?? 'secondary';
                        $last = $p->progress->last();
                        $persen = $last?->persen ?? 0;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->siswa->user->name ?? '-' }}<br><small class="text-muted">{{ $p->siswa->nis ?? '' }}</small></td>
                        <td>{{ $p->judul }}</td>
                        <td><small>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($p->target_selesai)->format('d M Y') }}</small></td>
                        <td>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                            </div>
                            <small>{{ $persen }}%</small>
                        </td>
                        <td><span class="badge bg-{{ $color }}">{{ str_replace('_',' ',$p->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection