@php
    $val = fn($f, $d = '') => old($f, $proyek?->$f ?? $d);
@endphp
<div class="row g-3">
    @if(empty($proyek))
        <div class="col-md-12">
            <label class="form-label">Siswa</label>
            <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
                <option value="">-- Pilih Siswa --</option>
                @foreach($siswa as $s)
                    <option value="{{ $s->id }}" @selected($val('siswa_id') == $s->id)>{{ $s->nis }} — {{ $s->user->name }}</option>
                @endforeach
            </select>
            @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endif
    <div class="col-md-12">
        <label class="form-label">Judul Proyek</label>
        <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ $val('judul') }}" required>
        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Deskripsi (opsional)</label>
        <textarea name="deskripsi" class="form-control" rows="3">{{ $val('deskripsi') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai" class="form-control" value="{{ $val('tanggal_mulai', date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Target Selesai</label>
        <input type="date" name="target_selesai" class="form-control" value="{{ $val('target_selesai', date('Y-m-d', strtotime('+1 month'))) }}" required>
    </div>
    @if(empty($proyek))
        <div class="col-md-12">
            <hr>
            <label class="form-label">Progress Awal (opsional)</label>
            <div class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="progress_awal[keterangan]" class="form-control" placeholder="Keterangan awal...">
                </div>
                <div class="col-md-3">
                    <input type="number" name="progress_awal[persen]" class="form-control" placeholder="%" min="0" max="100" value="0">
                </div>
            </div>
        </div>
    @endif
</div>