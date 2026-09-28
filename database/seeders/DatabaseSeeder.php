<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\Pembimbing;
use App\Models\PenempatanPkl;
use App\Models\PengaturanAbsensi;
use App\Models\Perusahaan;
use App\Models\Proyek;
use App\Models\ProyekProgress;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedUsers();
            $this->seedMasterData();
            $this->seedPengaturanAbsensi();
            $this->seedHistoris();
        });
    }

    private function seedUsers(): void
    {
        User::create([
            'name' => 'Admin Sistem',
            'username' => 'admin',
            'email' => 'admin@jurnal.test',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // User tambahan
        User::create([
            'name' => 'Operator Cadangan',
            'username' => 'operator',
            'email' => 'operator@jurnal.test',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);
    }

    private function seedMasterData(): void
    {
        // Sekolah
        $smk1 = Sekolah::create(['nama' => 'SMK Negeri 1 Contoh', 'npsn' => '12345678', 'alamat' => 'Jl. Contoh No. 1', 'telepon' => '021-123456']);
        $smk2 = Sekolah::create(['nama' => 'SMK Negeri 2 Contoh', 'npsn' => '87654321', 'alamat' => 'Jl. Contoh No. 2', 'telepon' => '021-654321']);

        // Perusahaan
        $ptA = Perusahaan::create(['nama' => 'PT Contoh Jaya', 'alamat' => 'Jl. Industri No. 1', 'bidang_usaha' => 'Software House', 'telepon' => '021-111111']);
        $ptB = Perusahaan::create(['nama' => 'CV Maju Bersama', 'alamat' => 'Jl. Industri No. 2', 'bidang_usaha' => 'Percetakan', 'telepon' => '021-222222']);

        // Guru (2 untuk tiap sekolah)
        $guruUser1 = User::create(['name' => 'Pak Budi', 'username' => 'budi', 'email' => 'budi@jurnal.test', 'password' => Hash::make('password'), 'role' => 'guru', 'is_active' => true]);
        $guru1 = Guru::create(['user_id' => $guruUser1->id, 'sekolah_id' => $smk1->id, 'nip' => '198501012010011001']);

        $guruUser2 = User::create(['name' => 'Bu Sari', 'username' => 'sari', 'email' => 'sari@jurnal.test', 'password' => Hash::make('password'), 'role' => 'guru', 'is_active' => true]);
        $guru2 = Guru::create(['user_id' => $guruUser2->id, 'sekolah_id' => $smk1->id, 'nip' => '198601012010011002']);

        $guruUser3 = User::create(['name' => 'Pak Andi', 'username' => 'andi', 'email' => 'andi@jurnal.test', 'password' => Hash::make('password'), 'role' => 'guru', 'is_active' => true]);
        $guru3 = Guru::create(['user_id' => $guruUser3->id, 'sekolah_id' => $smk2->id, 'nip' => '198701012010011003']);

        // Pembimbing (2 untuk tiap perusahaan)
        $pbUser1 = User::create(['name' => 'Pak Hartono', 'username' => 'hartono', 'email' => 'hartono@jurnal.test', 'password' => Hash::make('password'), 'role' => 'pembimbing', 'is_active' => true]);
        $pb1 = Pembimbing::create(['user_id' => $pbUser1->id, 'perusahaan_id' => $ptA->id, 'jabatan' => 'IT Manager']);

        $pbUser2 = User::create(['name' => 'Bu Lina', 'username' => 'lina', 'email' => 'lina@jurnal.test', 'password' => Hash::make('password'), 'role' => 'pembimbing', 'is_active' => true]);
        $pb2 = Pembimbing::create(['user_id' => $pbUser2->id, 'perusahaan_id' => $ptA->id, 'jabatan' => 'Senior Developer']);

        $pbUser3 = User::create(['name' => 'Pak Rudi', 'username' => 'rudi', 'email' => 'rudi@jurnal.test', 'password' => Hash::make('password'), 'role' => 'pembimbing', 'is_active' => true]);
        $pb3 = Pembimbing::create(['user_id' => $pbUser3->id, 'perusahaan_id' => $ptB->id, 'jabatan' => 'Kepala Produksi']);

        // Siswa (5 siswa di SMK1, 3 siswa di SMK2)
        $siswaSpecs = [
            ['Andi Wijaya', '1234567891', 'XII', 'RPL', $smk1->id, $ptA->id, $pb1->id, $guru1->id, 'andi.wijaya'],
            ['Budi Santoso', '1234567892', 'XII', 'RPL', $smk1->id, $ptA->id, $pb1->id, $guru1->id, 'budi.santoso'],
            ['Citra Dewi', '1234567893', 'XII', 'TKJ', $smk1->id, $ptA->id, $pb2->id, $guru2->id, 'citra.dewi'],
            ['Dewi Lestari', '1234567894', 'XII', 'RPL', $smk1->id, $ptB->id, $pb3->id, $guru2->id, 'dewi.lestari'],
            ['Eko Prabowo', '1234567895', 'XI', 'RPL', $smk1->id, $ptA->id, $pb1->id, $guru1->id, 'eko.prabowo'],
            ['Fitri Handayani', '1234567896', 'XII', 'TKJ', $smk2->id, $ptA->id, $pb2->id, $guru3->id, 'fitri.handayani'],
            ['Galih Pratama', '1234567897', 'XII', 'MM', $smk2->id, $ptB->id, $pb3->id, $guru3->id, 'galih.pratama'],
            ['Hana Pertiwi', '1234567898', 'XI', 'MM', $smk2->id, $ptB->id, $pb3->id, $guru3->id, 'hana.pertiwi'],
        ];

        foreach ($siswaSpecs as $i => [$name, $nis, $kelas, $jurusan, $smkId, $ptId, $pbId, $guruId, $username]) {
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $username . '@jurnal.test',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'is_active' => true,
            ]);
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'sekolah_id' => $smkId,
                'nis' => $nis,
                'kelas' => $kelas,
                'jurusan' => $jurusan,
                'telepon' => '08123456' . str_pad((string)(1000 + $i), 4, '0', STR_PAD_LEFT),
            ]);
            PenempatanPkl::create([
                'siswa_id'      => $siswa->id,
                'perusahaan_id' => $ptId,
                'pembimbing_id' => $pbId,
                'guru_id'       => $guruId,
                'tanggal_mulai' => Carbon::today()->subDays(20)->toDateString(),
                'tanggal_selesai' => Carbon::today()->addMonths(2)->toDateString(),
                'status' => 'aktif',
            ]);
        }

        // Simpan IDs untuk histori
        $this->siswaList = Siswa::with('user')->get();
    }

    private function seedPengaturanAbsensi(): void
    {
        $defaults = [
            ['perusahaan_id' => 1, 'hari' => 1, 'jam_masuk' => '07:30', 'batas_terlambat' => '08:00', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 1, 'hari' => 2, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 1, 'hari' => 3, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 1, 'hari' => 4, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 1, 'hari' => 5, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:30', 'libur' => false],
            ['perusahaan_id' => 1, 'hari' => 6, 'jam_masuk' => '08:30', 'batas_terlambat' => '09:00', 'jam_pulang_min' => '14:00', 'libur' => false],
            ['perusahaan_id' => 1, 'hari' => 7, 'jam_masuk' => '00:00', 'batas_terlambat' => '00:00', 'jam_pulang_min' => '00:00', 'libur' => true],
            ['perusahaan_id' => 2, 'hari' => 1, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 2, 'hari' => 2, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 2, 'hari' => 3, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 2, 'hari' => 4, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 2, 'hari' => 5, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '16:00', 'libur' => false],
            ['perusahaan_id' => 2, 'hari' => 6, 'jam_masuk' => '08:00', 'batas_terlambat' => '08:30', 'jam_pulang_min' => '14:00', 'libur' => false],
            ['perusahaan_id' => 2, 'hari' => 7, 'jam_masuk' => '00:00', 'batas_terlambat' => '00:00', 'jam_pulang_min' => '00:00', 'libur' => true],
        ];

        foreach ($defaults as $row) {
            PengaturanAbsensi::create($row);
        }
    }

    private function seedHistoris(): void
    {
        $siswas = $this->siswaList ?? Siswa::with('user')->get();

        foreach ($siswas as $siswa) {
            $penempatan = $siswa->penempatanAktif;
            if (!$penempatan) continue;

            for ($d = 14; $d >= 1; $d--) {
                $tanggal = Carbon::today()->subDays($d);
                if ($tanggal->isWeekend()) continue;

                $rand = rand(0, 100);
                if ($rand < 90) {
                    $jamMasuk = Carbon::parse($tanggal->format('Y-m-d') . ' 08:' . str_pad((string)rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00');
                    $jamPulang = Carbon::parse($tanggal->format('Y-m-d') . ' 16:00:00');
                    $status = $jamMasuk->format('H:i') > '08:30' ? 'terlambat' : 'hadir';

                    Absensi::create([
                        'siswa_id' => $siswa->id,
                        'penempatan_id' => $penempatan->id,
                        'tanggal' => $tanggal->toDateString(),
                        'jam_masuk' => $jamMasuk,
                        'jam_pulang' => $jamPulang,
                        'status' => $status,
                    ]);
                } elseif ($rand < 95) {
                    Absensi::create([
                        'siswa_id' => $siswa->id,
                        'penempatan_id' => $penempatan->id,
                        'tanggal' => $tanggal->toDateString(),
                        'jam_masuk' => $tanggal,
                        'status' => 'izin',
                        'keterangan' => 'Keperluan keluarga',
                    ]);
                } else {
                    Absensi::create([
                        'siswa_id' => $siswa->id,
                        'penempatan_id' => $penempatan->id,
                        'tanggal' => $tanggal->toDateString(),
                        'jam_masuk' => $tanggal,
                        'status' => 'sakit',
                        'keterangan' => 'Demam',
                    ]);
                }
            }

            // Jurnal histori (2-3 jurnal per minggu)
            for ($d = 14; $d >= 1; $d--) {
                $tanggal = Carbon::today()->subDays($d);
                if ($tanggal->isWeekend()) continue;

                $absensi = Absensi::where('siswa_id', $siswa->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();
                if (!$absensi || in_array($absensi->status, ['izin', 'sakit'])) continue;

                $judulSamples = [
                    'Setup development environment',
                    'Belajar Laravel routing',
                    'Implementasi CRUD',
                    'Review code dengan pembimbing',
                    'Debug bug authentication',
                    'Refactor controller ke service',
                    'Testing fitur absensi',
                    'Membuat laporan PDF',
                    'Diskusi arsitektur database',
                    'Presentasi progress ke guru',
                ];

                Jurnal::create([
                    'siswa_id' => $siswa->id,
                    'penempatan_id' => $penempatan->id,
                    'absensi_id' => $absensi->id,
                    'tanggal' => $tanggal->toDateString(),
                    'judul' => $judulSamples[array_rand($judulSamples)],
                    'deskripsi' => 'Hari ini saya mengerjakan ' . $judulSamples[array_rand($judulSamples)] . '. Banyak hal baru yang dipelajari.',
                    'kesulitan' => rand(0, 1) ? 'Kendala pada environment setup' : null,
                    'status' => $d > 7 ? 'done' : ($d > 3 ? 'done' : 'on_progress'),
                    'done_at' => $d > 7 ? Carbon::parse($tanggal->format('Y-m-d') . ' 16:00:00') : null,
                ]);
            }

            // Proyek: 1-2 proyek per siswa
            if (rand(0, 1)) {
                $proyek = Proyek::create([
                    'siswa_id' => $siswa->id,
                    'penempatan_id' => $penempatan->id,
                    'judul' => 'Buat Company Profile ' . $siswa->user->name,
                    'deskripsi' => 'Membuat website company profile dengan teknologi modern',
                    'diberikan_oleh' => Pembimbing::find($penempatan->pembimbing_id)?->user_id ?? 1,
                    'tanggal_mulai' => Carbon::today()->subDays(rand(5, 12))->toDateString(),
                    'target_selesai' => Carbon::today()->addDays(rand(15, 45))->toDateString(),
                    'status' => 'berjalan',
                ]);

                ProyekProgress::create([
                    'proyek_id' => $proyek->id,
                    'keterangan' => 'Project kick-off dan analisis kebutuhan',
                    'persen' => 20,
                ]);
                ProyekProgress::create([
                    'proyek_id' => $proyek->id,
                    'keterangan' => 'Desain UI dan database schema selesai',
                    'persen' => 50,
                ]);
            }
        }
    }
}