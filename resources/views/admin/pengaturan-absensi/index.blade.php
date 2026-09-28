@extends('layouts.admin')
@section('title', 'Pengaturan Absensi')
@section('page-title', 'Pengaturan Absensi')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.pengaturan-absensi.index') }}" class="mb-3 d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label">Pilih Perusahaan</label>
                <select name="perusahaan_id" class="form-select" onchange="this.form.submit()">
                    @foreach($perusahaan as $p)
                        <option value="{{ $p->id }}" @selected($selectedId == $p->id)>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline-secondary">Tampilkan</button>
        </form>

        <form method="POST" action="{{ route('admin.pengaturan-absensi.save') }}">
            @csrf
            <input type="hidden" name="perusahaan_id" value="{{ $selectedId }}">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Libur?</th>
                        <th>Jam Masuk</th>
                        <th>Batas Terlambat</th>
                        <th>Jam Pulang Min</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\PengaturanAbsensi::HARI as $i => $nama)
                        @php $row = $pengaturan[$i] ?? null; @endphp
                        <tr class="{{ ($row?->libur) ? 'table-light' : '' }}">
                            <td>
                                <strong>{{ $nama }}</strong>
                                <input type="hidden" name="settings[{{ $i }}][hari]" value="{{ $i }}">
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="settings[{{ $i }}][libur]" value="1" @checked($row?->libur) class="form-check-input libur-toggle" data-target="{{ $i }}">
                            </td>
                            <td><input type="time" name="settings[{{ $i }}][jam_masuk]" value="{{ $row?->jam_masuk ? \Carbon\Carbon::parse($row->jam_masuk)->format('H:i') : '08:00' }}" class="form-control"></td>
                            <td><input type="time" name="settings[{{ $i }}][batas_terlambat]" value="{{ $row?->batas_terlambat ? \Carbon\Carbon::parse($row->batas_terlambat)->format('H:i') : '09:00' }}" class="form-control"></td>
                            <td><input type="time" name="settings[{{ $i }}][jam_pulang_min]" value="{{ $row?->jam_pulang_min ? \Carbon\Carbon::parse($row->jam_pulang_min)->format('H:i') : '16:00' }}" class="form-control"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.libur-toggle').forEach(cb => {
    cb.addEventListener('change', () => {
        const tr = cb.closest('tr');
        tr.classList.toggle('table-light', cb.checked);
        tr.querySelectorAll('input[type=time]').forEach(i => i.disabled = cb.checked);
    });
});
document.querySelectorAll('.libur-toggle').forEach(cb => {
    const tr = cb.closest('tr');
    tr.querySelectorAll('input[type=time]').forEach(i => i.disabled = cb.checked);
});
</script>
@endpush