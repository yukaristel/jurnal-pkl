<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\JurnalController as AdminJurnalController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporan;
use App\Http\Controllers\Admin\PembimbingController;
use App\Http\Controllers\Admin\PenempatanController;
use App\Http\Controllers\Admin\PengaturanAbsensiController;
use App\Http\Controllers\Admin\PerusahaanController;
use App\Http\Controllers\Admin\ProyekController as AdminProyek;
use App\Http\Controllers\Admin\SekolahController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Guru\JurnalController as GuruJurnalController;
use App\Http\Controllers\Guru\LaporanController as GuruLaporan;
use App\Http\Controllers\Guru\PesanController as GuruPesanController;
use App\Http\Controllers\Guru\ProyekController as GuruProyek;
use App\Http\Controllers\Pembimbing\AbsensiController as PembimbingAbsensi;
use App\Http\Controllers\Pembimbing\CatatanController as PembimbingCatatan;
use App\Http\Controllers\Pembimbing\DashboardController as PembimbingDashboard;
use App\Http\Controllers\Pembimbing\JurnalController as PembimbingJurnalController;
use App\Http\Controllers\Pembimbing\LaporanController as PembimbingLaporan;
use App\Http\Controllers\Pembimbing\ProyekController as PembimbingProyek;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa\AbsensiController as SiswaAbsensi;
use App\Http\Controllers\Siswa\CatatanController as SiswaCatatan;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\Siswa\JurnalController as SiswaJurnalController;
use App\Http\Controllers\Siswa\LaporanController as SiswaLaporan;
use App\Http\Controllers\Siswa\ProyekController as SiswaProyek;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect(Auth::user()->dashboardRoute());
    }
    return view('welcome');
});

Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::resource('sekolah', SekolahController::class)->except(['show', 'create', 'edit']);
    Route::get('sekolah/{sekolah}/edit-json', [SekolahController::class, 'editJson'])->name('sekolah.editJson');
    Route::resource('perusahaan', PerusahaanController::class)->except(['show', 'create', 'edit']);
    Route::get('perusahaan/{perusahaan}/edit-json', [PerusahaanController::class, 'editJson'])->name('perusahaan.editJson');
    Route::resource('guru', GuruController::class)->except(['show', 'create', 'edit']);
    Route::get('guru/{guru}/edit-json', [GuruController::class, 'editJson'])->name('guru.editJson');
    Route::resource('pembimbing', PembimbingController::class)->except(['show', 'create', 'edit']);
    Route::get('pembimbing/{pembimbing}/edit-json', [PembimbingController::class, 'editJson'])->name('pembimbing.editJson');
    Route::resource('siswa', SiswaController::class)->except(['show', 'create', 'edit']);
    Route::get('siswa/{siswa}/edit-json', [SiswaController::class, 'editJson'])->name('siswa.editJson');
    Route::resource('penempatan', PenempatanController::class)->except(['show']);
    Route::get('pengaturan-absensi', [PengaturanAbsensiController::class, 'index'])->name('pengaturan-absensi.index');
    Route::post('pengaturan-absensi', [PengaturanAbsensiController::class, 'save'])->name('pengaturan-absensi.save');

    Route::get('jurnal', [AdminJurnalController::class, 'index'])->name('jurnal.index');
    Route::get('jurnal/{jurnal}', [AdminJurnalController::class, 'show'])->name('jurnal.show');
    Route::post('jurnal/{jurnal}/unlock', [AdminJurnalController::class, 'unlock'])->name('jurnal.unlock');

    Route::resource('proyek', AdminProyek::class)->only(['index', 'show', 'destroy']);

    Route::get('laporan', [AdminLaporan::class, 'index'])->name('laporan.index');
    Route::get('laporan/kehadiran', [AdminLaporan::class, 'kehadiran'])->name('laporan.kehadiran');
    Route::get('laporan/jurnal', [AdminLaporan::class, 'jurnal'])->name('laporan.jurnal');
    Route::get('laporan/proyek', [AdminLaporan::class, 'proyek'])->name('laporan.proyek');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboard::class, 'index'])->name('dashboard');
    Route::get('/absen/scan', [SiswaAbsensi::class, 'scan'])->name('absen.scan');
    Route::post('/absen/scan', [SiswaAbsensi::class, 'submitScan'])->name('absen.scan.submit');
    Route::post('/absen/pulang', [SiswaAbsensi::class, 'pulang'])->name('absen.pulang');
    Route::get('/absen/izin', [SiswaAbsensi::class, 'izinForm'])->name('absen.izin');
    Route::post('/absen/izin', [SiswaAbsensi::class, 'submitIzin'])->name('absen.izin.submit');

    Route::get('/jurnal', [SiswaJurnalController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/create', [SiswaJurnalController::class, 'create'])->name('jurnal.create');
    Route::post('/jurnal', [SiswaJurnalController::class, 'store'])->name('jurnal.store');
    Route::get('/jurnal/{jurnal}', [SiswaJurnalController::class, 'show'])->name('jurnal.show');
    Route::get('/jurnal/{jurnal}/edit', [SiswaJurnalController::class, 'edit'])->name('jurnal.edit');
    Route::put('/jurnal/{jurnal}', [SiswaJurnalController::class, 'update'])->name('jurnal.update');
    Route::post('/jurnal/{jurnal}/done', [SiswaJurnalController::class, 'markDone'])->name('jurnal.done');
    Route::post('/jurnal/{jurnal}/hold', [SiswaJurnalController::class, 'hold'])->name('jurnal.hold');
    Route::post('/jurnal/{jurnal}/reprogress', [SiswaJurnalController::class, 'reprogress'])->name('jurnal.reprogress');

    Route::get('/catatan', [SiswaCatatan::class, 'index'])->name('catatan.index');
    Route::get('/catatan/{catatan}', [SiswaCatatan::class, 'show'])->name('catatan.show');

    Route::get('/proyek', [SiswaProyek::class, 'index'])->name('proyek.index');
    Route::get('/proyek/{proyek}', [SiswaProyek::class, 'show'])->name('proyek.show');
    Route::post('/proyek/{proyek}/progress', [SiswaProyek::class, 'addProgress'])->name('proyek.progress');

    Route::get('/laporan', [SiswaLaporan::class, 'index'])->name('laporan.index');
    Route::get('/laporan/kehadiran', [SiswaLaporan::class, 'kehadiran'])->name('laporan.kehadiran');
    Route::get('/laporan/jurnal', [SiswaLaporan::class, 'jurnal'])->name('laporan.jurnal');
    Route::get('/laporan/proyek', [SiswaLaporan::class, 'proyek'])->name('laporan.proyek');
});

Route::middleware(['auth', 'role:pembimbing'])->prefix('pembimbing')->name('pembimbing.')->group(function () {
    Route::get('/dashboard', [PembimbingDashboard::class, 'index'])->name('dashboard');
    Route::get('/absensi/qr', [PembimbingAbsensi::class, 'qr'])->name('absensi.qr');
    Route::get('/absensi/token', [PembimbingAbsensi::class, 'tokenJson'])->name('absensi.token');

    Route::get('/jurnal', [PembimbingJurnalController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/{jurnal}', [PembimbingJurnalController::class, 'show'])->name('jurnal.show');

    Route::get('/jurnal/{jurnal}/catatan/create', [PembimbingCatatan::class, 'create'])->name('catatan.create');
    Route::post('/jurnal/{jurnal}/catatan', [PembimbingCatatan::class, 'store'])->name('catatan.store');

    Route::resource('proyek', PembimbingProyek::class);
    Route::post('proyek/{proyek}/progress', [PembimbingProyek::class, 'addProgress'])->name('proyek.progress');
    Route::post('proyek/{proyek}/status', [PembimbingProyek::class, 'setStatus'])->name('proyek.status');

    Route::get('laporan', [PembimbingLaporan::class, 'index'])->name('laporan.index');
    Route::get('laporan/kehadiran', [PembimbingLaporan::class, 'kehadiran'])->name('laporan.kehadiran');
    Route::get('laporan/jurnal', [PembimbingLaporan::class, 'jurnal'])->name('laporan.jurnal');
    Route::get('laporan/proyek', [PembimbingLaporan::class, 'proyek'])->name('laporan.proyek');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');
    Route::get('/jurnal', [GuruJurnalController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/{jurnal}', [GuruJurnalController::class, 'show'])->name('jurnal.show');

    Route::get('/pesan', [GuruPesanController::class, 'index'])->name('pesan.index');
    Route::post('/pesan', [GuruPesanController::class, 'store'])->name('pesan.store');

    Route::get('/proyek', [GuruProyek::class, 'index'])->name('proyek.index');
    Route::get('/proyek/{proyek}', [GuruProyek::class, 'show'])->name('proyek.show');

    Route::get('/laporan', [GuruLaporan::class, 'index'])->name('laporan.index');
    Route::get('/laporan/kehadiran', [GuruLaporan::class, 'kehadiran'])->name('laporan.kehadiran');
    Route::get('/laporan/jurnal', [GuruLaporan::class, 'jurnal'])->name('laporan.jurnal');
    Route::get('/laporan/proyek', [GuruLaporan::class, 'proyek'])->name('laporan.proyek');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';