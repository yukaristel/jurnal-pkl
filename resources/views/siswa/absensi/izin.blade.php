@extends('layouts.admin')
@section('title', 'Pengajuan Izin/Sakit')
@section('page-title', 'Ajukan Izin / Sakit')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="m-0">Form Izin / Sakit</h5>
            </div>
            <div class="card-body">
                @if($absensiHariIni)
                    <div class="alert alert-info">Anda sudah memiliki catatan absensi hari ini ({{ ucfirst($absensiHariIni->status) }}).</div>
                @else
                    <form method="POST" action="{{ route('siswa.absen.izin.submit') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select" required>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Alasan izin/sakit..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lampiran (opsional, max 2MB)</label>
                            <input type="file" name="lampiran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                        <a href="{{ route('siswa.dashboard') }}" class="btn btn-secondary">Batal</a>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection