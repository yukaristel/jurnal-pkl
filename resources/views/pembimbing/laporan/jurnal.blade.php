@extends('layouts.admin')
@section('title', 'Laporan Jurnal')
@section('page-title', 'Laporan Jurnal')

@section('content')
@include('laporan._filter', ['statusOptions' => ['on_progress'=>'On Progress','done'=>'Done','hold'=>'Hold','reprogress'=>'Reprogress']])

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <strong>Ringkasan:</strong>
        Total {{ $summary['total'] }} · On Progress {{ $summary['on_progress'] }} · Done {{ $summary['done'] }} · Hold {{ $summary['hold'] }} · Reprogress {{ $summary['reprogress'] }}
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>#</th><th>Tanggal</th><th>Siswa</th><th>Judul</th><th>Status</th><th>Catatan</th></tr></thead>
            <tbody>
                @forelse($rows as $i => $r)
                    @php $color = ['on_progress'=>'primary','done'=>'success','hold'=>'warning','reprogress'=>'secondary'][$r->status] ?? 'secondary'; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                        <td>{{ $r->siswa->user->name ?? '-' }}<br><small class="text-muted">{{ $r->siswa->nis ?? '' }}</small></td>
                        <td><strong>{{ $r->judul }}</strong><br><small class="text-muted">{{ \Illuminate\Support\Str::limit($r->deskripsi, 80) }}</small></td>
                        <td><span class="badge bg-{{ $color }}">{{ str_replace('_',' ',$r->status) }}</span></td>
                        <td>{{ $r->catatan->count() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection