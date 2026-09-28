# Jurnal PKL Siswa (Laravel 11)

Aplikasi web untuk memantau Praktik Kerja Lapangan (PKL) siswa. Siswa absen via scan barcode/QR dari pembimbing, mengisi jurnal harian, melihat catatan pembimbing & pesan guru. Guru memantau siswa dari sekolahnya saja. Superadmin mengelola seluruh data master.

## Stack

| Komponen | Pilihan |
|---|---|
| Framework | Laravel 11.x |
| Frontend | Blade + Bootstrap 5 (CDN) + Bootstrap Icons |
| Database | SQLite (default) / MySQL |
| Timezone | Asia/Jakarta |
| Barcode/QR | `simplesoftwareio/simple-qrcode` |
| Scan kamera | `html5-qrcode` (browser) + fallback input manual |
| Export PDF | `barryvdh/laravel-dompdf` |
| Auth | Laravel Breeze (Blade) + custom RoleMiddleware |
| DataTables | Server-side jQuery DataTables (CDN, tanpa library tambahan) |

## Peran Pengguna

| Role | Cakupan |
|---|---|
| `superadmin` | Semua data (CRUD sekolah, perusahaan, guru, pembimbing, siswa, penempatan, pengaturan absensi, lihat semua jurnal/proyek/laporan) |
| `guru` | Hanya siswa dari sekolahnya (`penempatan_pkl.guru_id`) |
| `pembimbing` | Hanya siswa bimbingannya (`penempatan_pkl.pembimbing_id`) |
| `siswa` | Hanya data miliknya sendiri |

## Setup Development

### Prasyarat
- PHP 8.2+ dengan ekstensi: `pdo_sqlite` (atau `pdo_mysql`), `mbstring`, `gd`, `bcmath`, `fileinfo`, `curl`, `openssl`
- Composer 2.x
- Node.js 18+ & npm (untuk build asset)

### Langkah

```bash
# 1. Install dependency
composer install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Migrasi + seeder (akan isi data demo)
php artisan migrate:fresh --seed --force

# 4. Storage symlink untuk upload file
php artisan storage:link

# 5. (Opsional) Build asset
npm install && npm run build

# 6. Jalankan server dev
php artisan serve
```

Akses `http://127.0.0.1:8000` di browser.

## User Demo (default setelah seeder)

| Role | Email / Username | Password |
|---|---|---|
| Superadmin | admin@jurnal.test / admin | password |
| Guru | budi@jurnal.test / budi | password |
| Guru | sari@jurnal.test / sari | password |
| Guru | andi@jurnal.test / andi | password |
| Pembimbing | hartono@jurnal.test / hartono | password |
| Pembimbing | lina@jurnal.test / lina | password |
| Pembimbing | rudi@jurnal.test / rudi | password |
| Siswa | andi.wijaya@jurnal.test / andi.wijaya | password |
| Siswa | budi.santoso@jurnal.test / budi.santoso | password |
| Siswa | ... 6 siswa lainnya | password |

Lokasi data: 2 sekolah (SMK N 1 & 2 Contoh), 2 perusahaan (PT Contoh Jaya & CV Maju Bersama), 8 siswa, 80 absensi historis 14 hari, 70 jurnal, 4 proyek.

## Struktur Folder

```
app/
├── Console/Commands/    # CloseAbsentWithoutPulang (auto-close)
├── DataTables/          # (legacy — pakai inline server-side di Controller)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/       # Dashboard, Sekolah, Perusahaan, Guru, Pembimbing, Siswa, Penempatan, PengaturanAbsensi, Jurnal, Proyek, Laporan
│   │   ├── Pembimbing/  # Dashboard, Absensi (QR), Jurnal, Catatan, Proyek, Laporan
│   │   ├── Guru/        # Dashboard, Jurnal, Pesan, Proyek, Laporan
│   │   ├── Siswa/       # Dashboard, Absensi (scan/pulang/izin), Jurnal, Catatan, Proyek, Laporan
│   │   └── Auth/        # Breeze auth
│   └── Middleware/RoleMiddleware.php
├── Models/              # 17 models
├── Policies/            # JurnalPolicy, AbsensiPolicy, ProyekPolicy, CatatanPolicy, PenempatanPolicy
├── Providers/AppServiceProvider.php  # Policy registration
└── Services/
    ├── AbsensiService.php     # QR generate, scan, pulang, izin
    ├── JurnalService.php      # CRUD jurnal, status transitions, reprogress
    ├── LaporanService.php     # Scope + 3 query laporan
    └── ProyekService.php      # CRUD proyek, progress, auto-status

database/
├── migrations/          # users (modified), sekolah, perusahaan, siswa, guru, pembimbing, penempatan_pkl, pengaturan_absensi (per hari), qr_token, absensi, jurnal, jurnal_lampiran, jurnal_status_log, catatan, proyek, proyek_progress, hari_libur
└── seeders/DatabaseSeeder.php  # Seeder lengkap dengan data historis

resources/views/
├── layouts/admin.blade.php       # Layout sidebar + topbar
├── layouts/partials/sidebar.blade.php  # Menu dinamis per role
├── admin/                         # Master data + pengaturan + monitoring
├── guru/                          # Read-only + pesan
├── pembimbing/                    # CRUD jurnal siswa + proyek + catatan + QR
├── siswa/                         # Dashboard, jurnal, proyek, catatan, absen
└── laporan/pdf/                   # Template PDF (kehadiran, jurnal, proyek)

routes/
├── web.php       # Resource route per role + custom
└── console.php   # Schedule command

tests/Feature/RoleAccessTest.php   # 14 tests lulus
```

## Konfigurasi Database (MySQL untuk Production)

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jurnal_pkl
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

Lalu:
```bash
php artisan migrate:fresh --seed --force
```

## Konfigurasi Cron (Production)

Tambahkan ke crontab:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Schedule aktif:
- `absensi:close-pending` — harian jam 00:05 (tutup absensi yang lupa pulang)

## Testing

```bash
php artisan test
```

Test saat ini: **14 test passed** untuk lintas role (skenario: guest redirect, role access, CRUD scope, lock jurnal, PDF export, dll).

## Deployment Notes (Production)

1. **Set environment production**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```

2. **Optimize**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

3. **Permission folder**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

4. **HTTPS wajib** untuk fitur kamera scan (`html5-qrcode` butuh HTTPS atau localhost).

5. **Web server**: Apache/nginx dengan `public/` sebagai document root. Contoh nginx:
   ```nginx
   location / { try_files $uri $uri/ /index.php?$query_string; }
   ```

6. **Backup database** harian (penting karena ada data absensi real-time).

## Limitasi / Known Issues

- **PDF format sederhana** — akan disempurnakan setelah ada format resmi dari sekolah
- **Tidak ada validasi GPS** saat absen (sesuai keputusan di dokumen)
- **Komunikasi satu arah** — pembimbing/guru → siswa (guru ↔ pembimbing belum)
- **Tidak ada upload bukti foto jurnal** (hanya proyek yang punya lampiran)
- **Email verification Breeze** dimatikan (gunakan `role` + `username` sebagai ganti `email`)

## Lisensi

Internal project. Sesuai dokumen `docs/jurnal-pkl-laravel.md`.