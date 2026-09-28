@extends('layouts.admin')
@section('title', 'Proyek')
@section('page-title', 'Proyek Siswa Bimbingan')

@section('content')
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Cari judul / siswa..." value="{{ request('q') }}"></div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">-- Semua Status --</option>
            @foreach(['belum_mulai','berjalan','selesai','batal'] as $st)
                <option value="{{ $st }}" @selected(request('status')==$st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    <div class="col-md-2 text-end"><a href="{{ route('pembimbing.proyek.create') }}" class="btn btn-success w-100"><i class="bi bi-plus-lg"></i> Beri Proyek</a></div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Judul</th><th>Siswa</th><th>Target</th><th>Progress</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($proyeks as $p)
                    @php
                        $last = $p->progress->last();
                        $persen = $last?->persen ?? 0;
                        $colors = ['belum_mulai' => 'secondary', 'berjalan' => 'primary', 'selesai' => 'success', 'batal' => 'danger'];
                    @endphp
                    <tr>
                        <td><strong>{{ $p->judul }}</strong></td>
                        <td>{{ $p->siswa->user->name ?? '-' }}<br><small class="text-muted">{{ $p->siswa->nis ?? '' }}</small></td>
                        <td>{{ \Carbon\Carbon::parse($p->target_selesai)->format('d M Y') }}</td>
                        <td>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $persen }}%"></div>
                            </div>
                            <small>{{ $persen }}%</small>
                        </td>
                        <td><span class="badge bg-{{ $colors[$p->status] ?? 'secondary' }}">{{ str_replace('_',' ',$p->status) }}</span></td>
                        <td><a href="{{ route('pembimbing.proyek.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada proyek.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $proyeks->links() }}</div>
</div>
@endsection