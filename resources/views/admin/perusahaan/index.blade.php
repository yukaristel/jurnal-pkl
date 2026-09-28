@extends('layouts.admin')
@section('title', 'Perusahaan')
@section('page-title', 'Data Perusahaan')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="m-0">Daftar Perusahaan</h5>
        <button class="btn btn-primary btn-sm" id="btn-tambah"><i class="bi bi-plus-lg"></i> Tambah Perusahaan</button>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered w-100" id="perusahaan-table">
            <thead>
                <tr><th>#</th><th>Nama</th><th>Bidang</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modal-perusahaan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-perusahaan" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Tambah Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nama Perusahaan</label><input type="text" name="nama" id="nama" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Bidang Usaha</label><input type="text" name="bidang_usaha" id="bidang_usaha" class="form-control"></div>
                    <div class="col-md-12"><label class="form-label">Alamat</label><textarea name="alamat" id="alamat" class="form-control" rows="2"></textarea></div>
                    <div class="col-md-4"><label class="form-label">Telepon</label><input type="text" name="telepon" id="telepon" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Latitude</label><input type="number" step="any" name="latitude" id="latitude" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Longitude</label><input type="number" step="any" name="longitude" id="longitude" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Radius (meter)</label><input type="number" name="radius_meter" id="radius_meter" class="form-control"></div>
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
    const table = $('#perusahaan-table').DataTable({
        processing: true, serverSide: true,
        ajax: "{{ route('admin.perusahaan.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'nama', name: 'nama'},
            {data: 'bidang_usaha', name: 'bidang_usaha'},
            {data: 'telepon', name: 'telepon'},
            {data: 'alamat', name: 'alamat'},
            {data: 'aksi', name: 'aksi', orderable: false, searchable: false},
        ],
        language: {url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'},
    });

    const modal = new bootstrap.Modal('#modal-perusahaan');
    const form = document.getElementById('form-perusahaan');
    const baseUrl = "{{ route('admin.perusahaan.index') }}";

    document.getElementById('btn-tambah').addEventListener('click', () => {
        form.reset();
        form.action = baseUrl;
        document.getElementById('method').value = 'POST';
        document.getElementById('modal-title').textContent = 'Tambah Perusahaan';
        modal.show();
    });

    $('#perusahaan-table').on('click', '.btn-edit', async function() {
        const id = this.dataset.id;
        const res = await fetch(`${baseUrl}/${id}/edit-json`, {headers: {'Accept': 'application/json'}});
        const data = await res.json();
        form.action = `${baseUrl}/${id}`;
        document.getElementById('method').value = 'PUT';
        document.getElementById('modal-title').textContent = 'Edit Perusahaan';
        ['nama','bidang_usaha','alamat','telepon','latitude','longitude','radius_meter'].forEach(k => {
            const el = document.getElementById(k);
            el.value = data[k] ?? '';
        });
        modal.show();
    });

    $('#perusahaan-table').on('click', '.btn-delete', async function() {
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