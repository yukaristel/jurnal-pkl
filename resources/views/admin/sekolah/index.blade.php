@extends('layouts.admin')
@section('title', 'Sekolah')
@section('page-title', 'Data Sekolah')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="m-0">Daftar Sekolah</h5>
        <button class="btn btn-primary btn-sm" id="btn-tambah"><i class="bi bi-plus-lg"></i> Tambah Sekolah</button>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered w-100" id="sekolah-table">
            <thead>
                <tr><th>#</th><th>Nama</th><th>NPSN</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-sekolah" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-sekolah" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Tambah Sekolah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" id="nama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">NPSN</label><input type="text" name="npsn" id="npsn" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" id="telepon" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="alamat" class="form-control" rows="2"></textarea></div>
                <div class="mb-3"><label class="form-label">Logo (opsional)</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const table = $('#sekolah-table').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('admin.sekolah.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'nama', name: 'nama'},
            {data: 'npsn', name: 'npsn'},
            {data: 'telepon', name: 'telepon'},
            {data: 'alamat', name: 'alamat'},
            {data: 'aksi', name: 'aksi', orderable: false, searchable: false},
        ],
        language: {url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'},
    });

    const modal = new bootstrap.Modal('#modal-sekolah');
    const form = document.getElementById('form-sekolah');
    const baseUrl = "{{ route('admin.sekolah.index') }}";

    document.getElementById('btn-tambah').addEventListener('click', () => {
        form.reset();
        form.action = baseUrl;
        document.getElementById('method').value = 'POST';
        document.getElementById('modal-title').textContent = 'Tambah Sekolah';
        modal.show();
    });

    $('#sekolah-table').on('click', '.btn-edit', async function() {
        const id = this.dataset.id;
        const res = await fetch(`${baseUrl}/${id}/edit-json`, {headers: {'Accept': 'application/json'}});
        const data = await res.json();
        form.action = `${baseUrl}/${id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('modal-title').textContent = 'Edit Sekolah';
        ['nama','npsn','telepon','alamat'].forEach(k => document.getElementById(k).value = data[k] ?? '');
        modal.show();
    });

    $('#sekolah-table').on('click', '.btn-delete', async function() {
        if (!confirm('Yakin hapus?')) return;
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        fd.append('_token', '{{ csrf_token() }}');
        await fetch(`${baseUrl}/${this.dataset.id}`, {method: 'POST', body: fd});
        table.ajax.reload();
    });

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(form);
        const res = await fetch(form.action, {method: 'POST', body: fd, headers: {'Accept': 'application/json'}});
        if (res.ok) { modal.hide(); table.ajax.reload(); }
        else { const data = await res.json(); alert(Object.values(data.errors).join('\n')); }
    });
});
</script>
@endpush