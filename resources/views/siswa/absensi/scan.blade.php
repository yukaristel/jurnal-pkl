@extends('layouts.admin')
@section('title', 'Scan Absen')
@section('page-title', 'Scan Barcode Absensi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="m-0">Scan Barcode Pembimbing</h5>
            </div>
            <div class="card-body">
                <div id="reader" style="width:100%; display:none;" class="mb-3"></div>

                <div class="mb-3">
                    <label class="form-label">Atau masukkan token manual:</label>
                    <div class="input-group">
                        <input type="text" id="token-input" class="form-control" placeholder="Tempel token dari QR...">
                        <button class="btn btn-primary" id="btn-submit">Kirim</button>
                    </div>
                    <small class="text-muted">Minta pembimbing menampilkan QR Code, scan dengan kamera HP, atau salin token jika tidak bisa scan.</small>
                </div>

                <div id="result"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
const resultBox = document.getElementById('result');
const tokenInput = document.getElementById('token-input');
const submitBtn = document.getElementById('btn-submit');
const submitUrl = "{{ route('siswa.absen.scan.submit') }}";
const csrf = "{{ csrf_token() }}";

function showAlert(ok, msg) {
    resultBox.innerHTML = `<div class="alert alert-${ok ? 'success' : 'danger'} alert-dismissible fade show">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function submitToken(token) {
    tokenInput.value = token;
    const res = await fetch(submitUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: JSON.stringify({token: token})
    });
    const data = await res.json();
    showAlert(data.ok, data.message);
    if (data.ok) {
        setTimeout(() => window.location.href = "{{ route('siswa.dashboard') }}", 1500);
    }
}

submitBtn.addEventListener('click', () => {
    const t = tokenInput.value.trim();
    if (!t) return;
    submitToken(t);
});

tokenInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') submitBtn.click();
});

if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    document.getElementById('reader').style.display = 'block';
    const html5QrCode = new Html5Qrcode("reader");
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            html5QrCode.start({facingMode: "environment"}, {fps: 10, qrbox: 250},
                (decodedText) => {
                    html5QrCode.stop();
                    submitToken(decodedText);
                },
                () => {}
            ).catch(() => {});
        }
    }).catch(() => {});
}
</script>
@endpush