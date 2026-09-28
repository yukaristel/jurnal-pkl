@php
    $val = fn($f, $d = '') => old($f, $jurnal?->$f ?? $d);
@endphp
<div class="mb-3">
    <label class="form-label">Judul Kegiatan</label>
    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ $val('judul') }}" required>
    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="deskripsi" rows="5" class="form-control @error('deskripsi') is-invalid @enderror" required>{{ $val('deskripsi') }}</textarea>
    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Kesulitan (opsional)</label>
    <textarea name="kesulitan" rows="3" class="form-control">{{ $val('kesulitan') }}</textarea>
</div>