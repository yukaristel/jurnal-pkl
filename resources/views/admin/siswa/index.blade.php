@extends('layouts.admin')
@section('title', 'Siswa')
@section('page-title', 'Data Siswa')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="m-0">Daftar Siswa</h5>
        <button class="btn btn-primary btn-sm" id="btn-tambah"><i class="bi bi-plus-lg"></i> Tambah Siswa</button>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered w-100" id="siswa-table">
            <thead>
                <tr><th>#</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jurusan</th><th>Sekolah</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-siswa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-siswa" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Tambah Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nama</label><input type="text" name="name" id="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">NIS</label><input type="text" name="nis" id="nis" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Username</label><input type="text" name="username" id="username" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="email" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" id="password" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Telepon</label><input type="text" name="telepon" id="telepon" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Kelas</label><input type="text" name="kelas" id="kelas" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Jurusan</label><input type="text" name="jurusan" id="jurusan" class="form-control"></div>
                    <div class="col-md-12">
                        <label class="form-label">Sekolah</label>
                        <select name="sekolah_id" id="sekolah_id" class="form-select" required>
                            <option value="">-- Pilih Sekolah --</option>
                            @foreach($sekolah as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
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
    const table = $('#siswa-table').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('admin.siswa.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'nis', name: 'nis'},
            {data: 'name', name: 'user.name'},
            {data: 'kelas', name: 'kelas'},
            {data: 'jurusan', name: 'jurusan'},
            {data: 'sekolah', name: 'sekolah.nama', orderable: false, searchable: false},
            {data: 'aksi', name: 'aksi', orderable: false, searchable: false},
        ],
        language: {url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'},
    });

    const modal = new bootstrap.Modal('#modal-siswa');
    const form = document.getElementById('form-siswa');
    const baseUrl = "{{ route('admin.siswa.index') }}";

    document.getElementById('btn-tambah').addEventListener('click', () => {
        form.reset();
        form.action = baseUrl;
        document.getElementById('method').value = 'POST';
        document.getElementById('modal-title').textContent = 'Tambah Siswa';
        document.getElementById('password').required = true;
        modal.show();
    });

    $('#siswa-table').on('click', '.btn-edit', async function() {
        const id = this.dataset.id;
        const res = await fetch(`${baseUrl}/${id}/edit-json`, {headers: {'Accept': 'application/json'}});
        const data = await res.json();
        form.action = `${baseUrl}/${id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('modal-title').textContent = 'Edit Siswa';
        document.getElementById('password').required = false;
        ['name','username','email','telepon'].forEach(k => document.getElementById(k).value = data.user[k] ?? '');
        ['nis','kelas','jurusan','sekolah_id'].forEach(k => document.getElementById(k).value = data[k] ?? '');
        modal.show();
    });

    $('#siswa-table').on('click', '.btn-delete', async function() {
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