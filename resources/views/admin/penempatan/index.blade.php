@extends('layouts.admin')
@section('title', 'Penempatan PKL')
@section('page-title', 'Data Penempatan PKL')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="m-0">Daftar Penempatan</h5>
        <a href="{{ route('admin.penempatan.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Penempatan</a>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered w-100" id="penempatan-table">
            <thead>
                <tr><th>#</th><th>Siswa</th><th>NIS</th><th>Perusahaan</th><th>Pembimbing</th><th>Guru</th><th>Periode</th><th>Status</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const table = $('#penempatan-table').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('admin.penempatan.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'siswa', name: 'siswa.user.name'},
            {data: 'nis', name: 'siswa.nis'},
            {data: 'perusahaan', name: 'perusahaan.nama'},
            {data: 'pembimbing', name: 'pembimbing.user.name'},
            {data: 'guru', name: 'guru.user.name'},
            {data: 'periode', name: 'tanggal_mulai', orderable: false, searchable: false},
            {data: 'status_badge', name: 'status', orderable: false, searchable: false},
            {data: 'aksi', name: 'aksi', orderable: false, searchable: false},
        ],
        language: {url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'},
    });

    $('#penempatan-table').on('click', '.btn-delete', async function() {
        if (!confirm('Yakin hapus?')) return;
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        fd.append('_token', '{{ csrf_token() }}');
        await fetch(`{{ route('admin.penempatan.index') }}/${this.dataset.id}`, {method: 'POST', body: fd});
        table.ajax.reload();
    });
});
</script>
@endpush