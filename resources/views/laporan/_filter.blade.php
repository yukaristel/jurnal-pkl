<form method="GET" class="row g-2 mb-3">
    @if($showSiswa ?? true)
    <div class="col-md-3">
        <label class="form-label small">Siswa</label>
        <select name="siswa_id" class="form-select form-select-sm">
            <option value="">-- Semua --</option>
            @foreach($scope as $s)
                <option value="{{ $s->id }}" @selected(request('siswa_id') == $s->id)>{{ $s->nis ?? '-' }} — {{ $s->user->name ?? '-' }}</option>
            @endforeach
        </select>
    </div>
    @endif
    @if($showPeriode ?? true)
    <div class="col-md-2">
        <label class="form-label small">Dari</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label small">Sampai</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
    </div>
    @endif
    @if(isset($statusOptions))
    <div class="col-md-2">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">-- Semua --</option>
            @foreach($statusOptions as $val => $label)
                <option value="{{ $val }}" @selected(request('status') == $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-md-3 d-flex gap-2 align-items-end">
        <button class="btn btn-primary btn-sm flex-grow-1">Filter</button>
        <a href="?{{ http_build_query(array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger btn-sm"><i class="bi bi-file-pdf"></i> PDF</a>
    </div>
</form>