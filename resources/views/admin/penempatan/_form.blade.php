@php
    $val = fn($field, $default = '') => old($field, $penempatan?->$field ?? $default);
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Siswa</label>
        <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
            <option value="">-- Pilih Siswa --</option>
            @foreach($siswa as $s)
                <option value="{{ $s->id }}" @selected($val('siswa_id') == $s->id)>{{ $s->nis }} — {{ $s->user->name }}</option>
            @endforeach
        </select>
        @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Perusahaan</label>
        <select name="perusahaan_id" class="form-select @error('perusahaan_id') is-invalid @enderror" required>
            <option value="">-- Pilih Perusahaan --</option>
            @foreach($perusahaan as $p)
                <option value="{{ $p->id }}" @selected($val('perusahaan_id') == $p->id)>{{ $p->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Pembimbing</label>
        <select name="pembimbing_id" class="form-select @error('pembimbing_id') is-invalid @enderror" required>
            <option value="">-- Pilih Pembimbing --</option>
            @foreach($pembimbing as $pb)
                <option value="{{ $pb->id }}" @selected($val('pembimbing_id') == $pb->id)>{{ $pb->user->name }} — {{ $pb->perusahaan->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Guru</label>
        <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
            <option value="">-- Pilih Guru --</option>
            @foreach($guru as $g)
                <option value="{{ $g->id }}" @selected($val('guru_id') == $g->id)>{{ $g->user->name }} — {{ $g->sekolah->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai" class="form-control" value="{{ $val('tanggal_mulai', date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal Selesai</label>
        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $val('tanggal_selesai', date('Y-m-d', strtotime('+2 months'))) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            @foreach(['aktif','selesai','batal'] as $st)
                <option value="{{ $st }}" @selected($val('status', 'aktif') == $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
</div>