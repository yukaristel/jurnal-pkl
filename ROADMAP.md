# Roadmap Pengembangan Aplikasi Jurnal PKL

Dokumen ini merangkum arah pengembangan aplikasi Jurnal PKL ke depan berdasarkan dokumen spesifikasi (`jurnal-pkl-laravel.md`) dan implementasi saat ini (v1.0 — semua 8 langkah selesai).

**Status implementasi v1.0**: ✓ Selesai
- Multi-role (superadmin, guru, pembimbing, siswa) dengan middleware + Policy
- CRUD master data (sekolah, perusahaan, guru, pembimbing, siswa, penempatan)
- Absensi QR Code dinamis (token30 detik) dengan scan kamera + input manual
- Jurnal harian dengan status (on_progress/done/hold/reprogress) + auto-hold saat pulang
- Catatan pembimbing & pesan guru (1 arah + auto-read tracking)
- Proyek dengan progress update + auto-status transitions
- Laporan (kehadiran, jurnal, proyek) + export PDF
- 14 PHPUnit tests lulus (RoleAccessTest + JurnalTest)
- Seeder lengkap (16 users, 2 sekolah, 2 perusahaan, 80 absensi, 70 jurnal historis)
- README + DEPLOY notes
- Auto-close absensi lupa pulang (scheduler harian jam00:05)

---

## V1.1 — Quick Wins (1-2 minggu)

Fungsionalitas kecil yang langsung dibutuhkan user berdasarkan hasil testing.

### 1. Notifikasi In-App (Laravel Notifications)
- Pembimbing berkomentar di jurnal → siswa dapat notifikasi bell icon di topbar
- Guru mengirim pesan ke siswa → notifikasi bell + badge unread
- Superadmin unlock jurnal → notifikasi ke siswa pemilik jurnal
- **Implementasi**: pakai tabel `notifications` bawaan Laravel + custom channel database

### 2. Filter & Sort DataTables di Halaman Admin
- Tabel data di `/admin/siswa`, `/admin/guru`, dll saat ini masih dari server-side tapi belum ada sort per kolom
- Tambah sort by name/NIS/email + filter per sekolah/perusahaan
- Pencarian global lebih cepat dengan `defer()`

### 3. Pagination di Recent Activity Dashboard
- Recent activity (5 absensi, 5 jurnal) di dashboard admin/guru/pembimbing hanya tampil5 item
- Tambah "Lihat Semua" + pagination atau infinite scroll

### 4. Edit Profil User (Semua Role)
- Breeze punya `profile.edit` route tapi saat ini belum terhubung di menu
- Tambah menu "Profil" di sidebar (semua role) → form edit nama, email, telepon, ganti password
- Validasi: unique email tapi ignore current user

### 5. Notification Bell di Topbar
- Ikon bell dengan badge unread count di topbar (semua role)
- Dropdown menampilkan 5 notifikasi terbaru + link ke halaman masing-masing
- Polling setiap 30 detik untuk update count

---

## V1.2 — Peningkatan UX/UI (2-3 minggu)

### 1. Mobile-First Redesign
- Responsive belum optimal di layar< 768px (sidebar masih full-width)
- Bottom navigation untuk mobile (alternatif sidebar)
- Touch-friendly buttons (min44×44px) untuk scan QR & absen pulang

### 2. Dark Mode Toggle
- Tailwind sudah punya `dark:` prefix, tinggal implement di layout
- Simpan preferensi di `localStorage` + cookie
- Auto-detect `prefers-color-scheme`

### 3. Print-Friendly Views
- Tambah `@media print` styling untuk laporan & detail jurnal
- Print langsung dari browser tanpa dialog print dialog yang berantakan

### 4. Loading States & Skeleton Screens
- Saat AJAX call (scan QR, pulang), tampilkan spinner + disable button
- Skeleton loading untuk tabel yang paginated

### 5. Empty States yang Informatif
- Banyak halaman menampilkan "Belum ada data" tanpa ilustrasi/CTA
- Tambah empty state dengan icon + pesan + tombol ajakan (misal "Buat Jurnal Pertama")

---

## V1.3 — Fitur Bisnis Tambahan (1-2 bulan)

### 1. Penilaian / Evaluasi Siswa oleh Pembimbing & Guru
- Form penilaian per siswa (aspek: kedisiplinan, kemampuan teknis, softskill, inisiatif)
- Skala 1-5 atau 100 + komentar naratif
- Laporan penilaian per siswa / per kelas / per periode
- Export PDF rapor penilaian

### 2. Komunikasi Guru ↔ Pembimbing (2 Arah)
- Saat ini cuma 1 arah (pembimbing/guru → siswa)
- Tambah kolom chat/pesan antara guru sekolah & pembimbing perusahaan
- Topik diskusi: progress siswa, masalah kehadiran, dll

### 3. Jurnal Mingguan / Refleksi
- Selain jurnal harian, siswa wajib submit refleksi mingguan
- Template tetap: "Apa yang saya pelajari minggu ini?", "Kendala", "Rencana minggu depan"
- Guru/pembimbing bisa comment per refleksi

### 4. Kalender Akademik & Hari Libur
- Tambah tabel `kalender_akademik` dengan event: ujian, libur nasional, event sekolah
- Hari libur otomatis exclude dari absensi (status: libur)
- Tampilan kalender di dashboard untuk siswa & pembimbing

### 5. Upload Bukti Foto Jurnal (Multi-File)
- Saat ini jurnal tidak punya lampiran (hanya proyek)
- Siswa bisa upload foto dokumentasi kegiatan (max 5 foto per jurnal)
- Tampil sebagai gallery lightbox di detail jurnal
- Storage: `storage/app/public/jurnal-lampiran/{jurnal_id}/`

### 6. Tracking Kesehatan & Catatan Kendala
- Siswa bisa catat kendala teknis/non-teknis yang dialami
- Pembimbing bisa assign "todo" ke siswa terkait proyek tertentu
- Reminder untuk siswa yang belum isi jurnal >1 hari

---

## V2.0 — API & Mobile (3-6 bulan)

### 1. REST API untuk Mobile App
- Sanctum token auth (login pakai username)
- Endpoints: `/api/v1/absensi/scan`, `/api/v1/jurnal`, `/api/v1/proyek`, dll
- Dokumentasi dengan Scribe atau OpenAPI spec
- Rate limiting per user

### 2. Native Mobile App (Flutter / React Native)
- Scan QR langsung dari kamera native (lebih cepat dari web html5-qrcode)
- Push notification (FCM/APNs) untuk catatan baru dari pembimbing
- Offline mode: queue absensi scan + sync saat online
- Biometric login (fingerprint/face ID)

### 3. Progressive Web App (PWA)
- Service worker untuk offline-first
- Install sebagai "app" di home screen
- Push notification via web push API

---

## V2.1 — Analytics & Reporting (1-2 bulan)

### 1. Dashboard Analytics untuk Kepala Sekolah
- Agregat multi-sekolah: perbandingan absensi antar sekolah
- Trend dropout / siswa yang tidak lulus PKL
- Statistik perusahaan yang paling banyak menerima siswa
- Prediksi kelulusan berdasarkan pola absensi

### 2. Custom Report Builder
- User bisa pilih kolom, filter, group by sendiri
- Save sebagai template report
- Schedule report auto-email ke kepala sekolah setiap Senin pagi

### 3. Grafik Performa Siswa
- Line chart jurnal harian / mingguan per siswa
- Heatmap kehadiran (calendar view) per bulan
- Radar chart penilaian (multi-dimensi)
- Trend proyek (progress %, deadline compliance)

### 4. Export Multi-Format
- Saat ini cuma PDF; tambah Excel (.xlsx) dan CSV
- Laporan gabungan absensi + jurnal dalam1 file Excel dengan multiple sheets
- Print to PDF rapikan (kop sekolah, nomor halaman, watermark)

---

## V2.2 — Integrasi & Otomasi (2-3 bulan)

### 1. WhatsApp Gateway Integration
- Notifikasi absensi via WA ke orang tua (jika diizinkan)
- Reminder pulang untuk siswa
- Laporan mingguan ke orang tua
- Provider: Wablas, Twilio, atau Fonnte

### 2. Email Digest Harian
- Setiap pagi, email ke guru: daftar siswa yang belum absen jam 08:30
- Setiap sore, email ke orang tua: rekap absensi anak hari itu
- Scheduler harian otomatis

### 3. Single Sign-On (SSO)
- Integrasi dengan Google Workspace / Microsoft 365
- Login pakai akun sekolah yang sudah ada
- Auto-provision user baru dari LDAP/Active Directory sekolah

### 4. Google Classroom / Microsoft Teams Integration
- Sync jurnal ke Google Classroom assignment
- Video call dengan Teams untuk meeting pembimbing-siswa

### 5. SIAP-PKL / Feeder Integration
- Konek ke sistem informasi akademik sekolah (Feeder PDDIKTI / SIAP-PKL)
- Tarik data NISN, nama, kelas otomatis
- Push nilai PKL kembali ke rapor sekolah

---

## V3.0 — AI & Machine Learning (6-12 bulan)

### 1. Prediksi Kehadiran
- ML model untuk prediksi siswa yang kemungkinan alpha/terlambat
- Berdasarkan history kehadiran, cuaca, hari dalam seminggu, event sekolah
- Alert dini ke guru/wali kelas

### 2. Chatbot AI untuk Siswa
- LLM (GPT-4 / Claude) sebagai asisten siswa PKL
- Tanya jawab: "Bagaimana cara menulis jurnal yang baik?"
- Rekomendasi kegiatan harian berdasarkan proyek yang sedang berjalan

### 3. Auto-Generate Catatan Pembimbing
- AI suggests feedback untuk jurnal siswa berdasarkan isi jurnal
- Pembimbing tinggal review & edit, hemat waktu

### 4. Analisis Sentimen Catatan
- Deteksi dini masalah siswa dari catatan pembimbing/guru (kata-kata negatif)
- Alert ke guru BK jika perlu intervensi

### 5. Smart Scheduling PKL
- AI recommend penempatan siswa berdasarkan skill, lokasi, preferensi
- Optimasi distribusi siswa ke perusahaan (balance load)
- Prediksi kapasitas perusahaan berdasarkan history

---

## V3.1 — Fitur Enterprise (6-12 bulan)

### 1. Multi-Tenant untuk Banyak Sekolah
- Saat ini single-deployment per sekolah
- Tenant isolation: 1 server handle banyak sekolah dengan database terpartisi
- Branding per sekolah (logo, warna primary)
- Billing / subscription management

### 2. Compliance & Audit
- Log semua perubahan data (siapa, kapan, apa)
- GDPR-compliant data retention policy
- Export data siswa sesuai permintaan (right to be forgotten)
- Backup otomatis dengan encryption

### 3. Performance & Scalability
- Redis untuk session & cache (saat ini file-based)
- Queue worker dengan supervisor (untuk email, PDF generation, scheduled tasks)
- CDN untuk static assets
- Load balancer untuk high availability
- Database read replica untuk laporan

### 4. Microservices Architecture (Optional)
- Pecah jadi service: Auth, Absensi, Jurnal, Proyek, Laporan
- Communication via REST/gRPC + message queue (RabbitMQ/Kafka)
- Kubernetes deployment

### 5. Advanced Security
- 2FA (TOTP / SMS)
- IP whitelisting per role (guru cuma akses dari sekolah)
- Rate limiting agresif untuk prevent brute force
- Audit log untuk sensitive operations (unlock jurnal, dll)

---

## Technical Debt & Refactoring (Ongoing)

Yang perlu dirapikan di codebase saat ini:

### 1. Code Quality
- Banyak logic business di controller — extract ke Service class yang sudah ada pattern-nya (JurnalService, AbsensiService, ProyekService, LaporanService)
- Tambah interface & contract untuk testability
- Tambah unit test per Service class (sekarang hanya feature test)

### 2. Database
- Beberapa tabel belum ada index optimal — audit dengan `EXPLAIN QUERY`
- Pertimbangkan partitioning untuk tabel `absensi` & `jurnal` kalau data >1 juta rows
- Soft delete (`deleted_at`) untuk tabel master data (sekolah, perusahaan, dll) — saat ini hard delete

### 3. API Consistency
- Response format belum seragam (ada yang return array, ada yang object)
- Bikin API Resource class (`JsonResource`) untuk konsistensi jika nanti ada mobile app
- Standard error response format `{success: false, message: "...", errors: {...}}`

### 4. Frontend Modernization
- Saat ini pakai Bootstrap 5 via CDN + jQuery DataTables
- Migrate ke **Livewire 3** atau **Inertia.js + Vue/React** untuk SPA-like UX tanpa full SPA
- Component-based admin panel dengan reusable components

### 5. Testing Coverage
- Saat ini baru 14 tests, perlu minimal 70% coverage
- Tambah Browser test (Laravel Dusk) untuk flow E2E (login → scan → isi jurnal → pulang)
- Tambah Performance test (assert response time<500ms untuk halaman utama)

---

## Prioritas Implementasi (Rekomendasi 6 Bulan Pertama)

| Bulan | Fokus | Deliverable |
|---|---|---|
| **Bulan 1-2** | V1.1 + V1.2 | Notifikasi in-app, filter tables, dark mode, mobile responsive |
| **Bulan 3-4** | V1.3 (subset) | Penilaian, komunikasi 2 arah, upload foto jurnal, kalender |
| **Bulan 5-6** | V2.0 MVP | REST API + PWA (mobile-first) sebagai ganti native app |

**Setelah itu** pilih sesuai prioritas user:
- **Sekolah**: fokus ke V1.3 (fitur bisnis) + V2.1 (analytics)
- **Multi-sekolah (commercial)**: fokus ke V3.0 (multi-tenant + billing)
- **Modern tech-savvy**: fokus ke V2.0 + V3.0 (mobile + AI)

---

## Metrik Sukses

Tracking KPI untuk validasi roadmap:

| Metrik | Target |
|---|---|
| Daily active users (DAU) | >80% siswa, >50% guru/pembimbing |
| Absensi tepat waktu | >90% siswa scan QR jam 07:30-08:00 |
| Jurnal completion | >95% siswa submit jurnal harian |
| Catatan feedback | Rata-rata >2 catatan/jurnal dari pembimbing |
| User satisfaction | NPS >50 dari survey semesteran |
| System uptime | 99.9% (<9 jam downtime/tahun) |
| Response time | <500ms untuk 95% page load |
| Test coverage | >70% |

---

## Estimasi Resource

| Role | Jumlah | Komentar |
|---|---|---|
| Backend Laravel developer | 1-2 | Full-time untuk core features |
| Frontend developer | 1 | Bisa overlap dengan backend untuk blade |
| UI/UX designer | 0.5 | Part-time untuk redesign mobile & dashboard |
| QA / Tester | 0.5 | Manual + automated test |
| DevOps | 0.5 | Untuk CI/CD, monitoring, scaling |
| Product Manager | 0.5 | Roadmap prioritization |

**Budget estimasi kasar** (Indonesia, 2026):
- MVP V1.1-V1.3 (~3 bulan): Rp 150-300 juta
- V2.0 + V2.1 (~3 bulan): Rp 300-500 juta
- V3.0+ (AI, multi-tenant, ~6 bulan): Rp 500-800 juta

---

## Catatan Penutup

Roadmap ini **living document** — update setiap quarter berdasarkan:
- Feedback user (guru, pembimbing, siswa)
- Issue yang muncul di production
- Tren teknologi (AI, mobile, dsb)
- Kebutuhan bisnis baru (misal: integrasi dengan dinas pendidikan)

Setiap fitur baru harus:
1. **Solve real problem** (bukan nice-to-have tanpa use case jelas)
2. **Validated by user** sebelum invest besar
3. **Measurable impact** (metrik sukses jelas)
4. **Maintainable** (tambah tech debt seminimal mungkin)

Untuk diskusi roadmap lebih lanjut atau mengusulkan fitur baru, hubungi product owner atau buka issue di repository.

---

**Dibuat**: 25 September 2026
**Versi dokumen**: 1.0
**Aplikasi versi**: v1.0 (full implementasi 8 langkah dokumen spec)