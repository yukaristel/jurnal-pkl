@php
    $role = auth()->user()->role;
    $prefix = match($role) {
        'superadmin' => 'admin',
        'guru' => 'guru',
        'pembimbing' => 'pembimbing',
        'siswa' => 'siswa',
        default => '',
    };
@endphp
<aside class="app-sidebar">
    <div class="brand">
        <i class="bi bi-journal-bookmark-fill"></i>
        {{ config('app.name') }}
    </div>

    @if($role === 'superadmin')
        <div class="nav-section">Master Data</div>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('admin.sekolah.*') ? 'active' : '' }}" href="{{ route('admin.sekolah.index') }}"><i class="bi bi-building"></i> Sekolah</a>
        <a class="nav-link {{ request()->routeIs('admin.perusahaan.*') ? 'active' : '' }}" href="{{ route('admin.perusahaan.index') }}"><i class="bi bi-briefcase"></i> Perusahaan</a>
        <a class="nav-link {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}" href="{{ route('admin.guru.index') }}"><i class="bi bi-person-badge"></i> Guru</a>
        <a class="nav-link {{ request()->routeIs('admin.pembimbing.*') ? 'active' : '' }}" href="{{ route('admin.pembimbing.index') }}"><i class="bi bi-person-workspace"></i> Pembimbing</a>
        <a class="nav-link {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}" href="{{ route('admin.siswa.index') }}"><i class="bi bi-mortarboard"></i> Siswa</a>
        <a class="nav-link {{ request()->routeIs('admin.penempatan.*') ? 'active' : '' }}" href="{{ route('admin.penempatan.index') }}"><i class="bi bi-diagram-3"></i> Penempatan PKL</a>
        <div class="nav-section">Pengaturan</div>
        <a class="nav-link {{ request()->routeIs('admin.pengaturan-absensi.*') ? 'active' : '' }}" href="{{ route('admin.pengaturan-absensi.index') }}"><i class="bi bi-clock-history"></i> Pengaturan Absensi</a>
        <div class="nav-section">Monitoring</div>
        <a class="nav-link {{ request()->routeIs('admin.jurnal.*') ? 'active' : '' }}" href="{{ route('admin.jurnal.index') }}"><i class="bi bi-journal-text"></i> Semua Jurnal</a>
        <a class="nav-link {{ request()->routeIs('admin.proyek.*') ? 'active' : '' }}" href="{{ route('admin.proyek.index') }}"><i class="bi bi-kanban"></i> Semua Proyek</a>
        <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" href="{{ route('admin.laporan.index') }}"><i class="bi bi-file-earmark-pdf"></i> Laporan</a>
    @elseif($role === 'siswa')
        <a class="nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}" href="{{ route('siswa.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('siswa.jurnal.*') ? 'active' : '' }}" href="{{ route('siswa.jurnal.index') }}"><i class="bi bi-journal-text"></i> Jurnal Hari Ini</a>
        <a class="nav-link {{ request()->routeIs('siswa.proyek.*') ? 'active' : '' }}" href="{{ route('siswa.proyek.index') }}"><i class="bi bi-kanban"></i> Proyek Saya</a>
        <a class="nav-link {{ request()->routeIs('siswa.catatan.*') ? 'active' : '' }}" href="{{ route('siswa.catatan.index') }}"><i class="bi bi-chat-dots"></i> Catatan & Pesan</a>
        <a class="nav-link {{ request()->routeIs('siswa.laporan.*') ? 'active' : '' }}" href="{{ route('siswa.laporan.index') }}"><i class="bi bi-file-earmark-pdf"></i> Laporan</a>
        <a class="nav-link {{ request()->routeIs('siswa.absen.scan') ? 'active' : '' }}" href="{{ route('siswa.absen.scan') }}"><i class="bi bi-qr-code-scan"></i> Scan Absen</a>
        <a class="nav-link {{ request()->routeIs('siswa.absen.izin') ? 'active' : '' }}" href="{{ route('siswa.absen.izin') }}"><i class="bi bi-file-medical"></i> Izin/Sakit</a>
    @elseif($role === 'guru')
        <a class="nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}" href="{{ route('guru.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('guru.jurnal.*') ? 'active' : '' }}" href="{{ route('guru.jurnal.index') }}"><i class="bi bi-journal-text"></i> Jurnal Siswa</a>
        <a class="nav-link {{ request()->routeIs('guru.proyek.*') ? 'active' : '' }}" href="{{ route('guru.proyek.index') }}"><i class="bi bi-kanban"></i> Proyek Siswa</a>
        <a class="nav-link {{ request()->routeIs('guru.pesan.*') ? 'active' : '' }}" href="{{ route('guru.pesan.index') }}"><i class="bi bi-envelope"></i> Pesan ke Siswa</a>
        <a class="nav-link {{ request()->routeIs('guru.laporan.*') ? 'active' : '' }}" href="{{ route('guru.laporan.index') }}"><i class="bi bi-file-earmark-pdf"></i> Laporan</a>
    @elseif($role === 'pembimbing')
        <a class="nav-link {{ request()->routeIs('pembimbing.dashboard') ? 'active' : '' }}" href="{{ route('pembimbing.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('pembimbing.absensi.*') ? 'active' : '' }}" href="{{ route('pembimbing.absensi.qr') }}"><i class="bi bi-qr-code"></i> QR Absensi</a>
        <a class="nav-link {{ request()->routeIs('pembimbing.jurnal.*') ? 'active' : '' }}" href="{{ route('pembimbing.jurnal.index') }}"><i class="bi bi-journal-text"></i> Jurnal Siswa</a>
        <a class="nav-link {{ request()->routeIs('pembimbing.proyek.*') ? 'active' : '' }}" href="{{ route('pembimbing.proyek.index') }}"><i class="bi bi-kanban"></i> Proyek Siswa</a>
        <a class="nav-link {{ request()->routeIs('pembimbing.laporan.*') ? 'active' : '' }}" href="{{ route('pembimbing.laporan.index') }}"><i class="bi bi-file-earmark-pdf"></i> Laporan</a>
    @endif
</aside>