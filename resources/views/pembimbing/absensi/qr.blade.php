@extends('layouts.admin')
@section('title', 'QR Absensi')
@section('page-title', 'Barcode Absensi')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="m-0">QR Code Aktif</h5>
                <span class="badge bg-primary" id="badge-token">Memuat…</span>
            </div>
            <div class="card-body text-center">
                <div id="qr-container" class="d-flex justify-content-center my-3">
                    <div class="border rounded p-3 bg-white" style="width: 320px; height: 320px; display:flex; align-items:center; justify-content:center;">
                        <div class="text-muted">Memuat QR Code…</div>
                    </div>
                </div>
                <div class="text-muted">
                    Token akan diperbarui otomatis setiap <strong>30 detik</strong>.
                </div>
                <div class="mt-2">
                    <small class="text-muted">Sisa waktu: <span id="countdown">--</span></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="m-0">Siswa Hadir Hari Ini ({{ \Carbon\Carbon::today()->format('d M Y') }})</h5>
            </div>
            <div class="card-body">
                @if($siswaAbsenHariIni->isEmpty())
                    <p class="text-muted text-center">Belum ada siswa yang absen.</p>
                @else
                    <ul class="list-group">
                        @foreach($siswaAbsenHariIni as $a)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $a->siswa->user->name ?? '-' }}</strong>
                                    <small class="text-muted d-block">{{ $a->siswa->nis ?? '-' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $a->status === 'terlambat' ? 'warning' : 'success' }}">{{ ucfirst($a->status) }}</span>
                                    <small class="d-block text-muted">{{ \Carbon\Carbon::parse($a->jam_masuk)->format('H:i:s') }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tokenUrl = "{{ route('pembimbing.absensi.token') }}";
let countdown = 30;

async function refreshQr() {
    try {
        const res = await fetch(tokenUrl, {headers: {'Accept': 'application/json'}});
        const data = await res.json();
        document.getElementById('qr-container').innerHTML = `<div class="border rounded p-3 bg-white">${data.qr_svg}</div>`;
        document.getElementById('badge-token').textContent = 'Token aktif';
        countdown = data.remaining;
        document.getElementById('countdown').textContent = countdown + ' detik';
    } catch (e) {
        document.getElementById('badge-token').textContent = 'Error';
    }
}

setInterval(() => {
    countdown--;
    if (countdown <= 0) {
        refreshQr();
    } else {
        document.getElementById('countdown').textContent = countdown + ' detik';
    }
}, 1000);

setInterval(refreshQr, 30000);
refreshQr();
</script>
@endpush