@extends('layouts.admin')
@section('title', 'Jurnal Siswa')
@section('page-title', 'Jurnal Siswa (Sekolah)')

@section('content')
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Cari judul / siswa / NIS..." value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">-- Semua Status --</option>
            @foreach(['on_progress','done','hold','reprogress'] as $st)
                <option value="{{ $st }}" @selected(request('status') == $st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Judul</th><th>Status</th><th>Catatan</th><th></th></tr></thead>
            <tbody>
                @forelse($jurnals as $j)
                @php $c = match($j->status) { 'on_progress' => 'primary', 'done' => 'success', 'hold' => 'warning', default => 'secondary' }; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($j->tanggal)->format('d M Y') }}</td>
                    <td><strong>{{ $j->siswa->user->name ?? '-' }}</strong><br><small class="text-muted">{{ $j->siswa->nis ?? '' }}</small></td>
                    <td>{{ $j->judul }}</td>
                    <td><span class="badge bg-{{ $c }}">{{ str_replace('_',' ', $j->status) }}</span></td>
                    <td>{{ $j->catatan->count() }}</td>
                    <td><a href="{{ route('guru.jurnal.show', $j) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada jurnal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $jurnals->links() }}</div>
</div>
@endsection