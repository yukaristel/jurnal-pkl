<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\PenempatanPkl;
use App\Models\Siswa;
use App\Models\User;
use App\Services\JurnalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurnalTest extends TestCase
{
    use RefreshDatabase;

    private JurnalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--force' => true]);
        $this->service = app(JurnalService::class);
    }

    private function siswaWithAbsen(): array
    {
        $siswaUser = User::where('role', 'siswa')->firstOrFail();
        $siswa = $siswaUser->siswa;

        // Pastikan ada absen hadir hari ini
        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'penempatan_id' => $siswa->penempatanAktif->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'status' => 'hadir',
        ]);

        return [$siswaUser, $siswa, $absensi];
    }

    public function test_can_create_jurnal_after_absen(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $this->actingAs($siswaUser)
            ->post('/siswa/jurnal', [
                'judul' => 'Belajar Laravel',
                'deskripsi' => 'Mempelajari routing dan controller.',
                'kesulitan' => 'Sedikit bingung di awal.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('jurnal', [
            'siswa_id' => $siswa->id,
            'judul' => 'Belajar Laravel',
            'status' => 'on_progress',
        ]);

        // Status log harus tercatat
        $jurnal = Jurnal::latest('id')->first();
        $this->assertDatabaseHas('jurnal_status_log', [
            'jurnal_id' => $jurnal->id,
            'ke_status' => 'on_progress',
        ]);
    }

    public function test_cannot_create_jurnal_without_absen(): void
    {
        $siswaUser = User::where('role', 'siswa')->firstOrFail();
        $siswa = $siswaUser->siswa;

        // Hapus absen hari ini kalau ada
        Absensi::where('siswa_id', $siswa->id)->where('tanggal', today())->delete();

        $countBefore = Jurnal::where('siswa_id', $siswa->id)->count();

        $response = $this->actingAs($siswaUser)
            ->post('/siswa/jurnal', [
                'judul' => 'Tanpa Absen',
                'deskripsi' => 'Test',
            ]);

        $response->assertSessionHas('error');
        $this->assertSame($countBefore, Jurnal::where('siswa_id', $siswa->id)->count());
    }

    public function test_can_edit_own_jurnal_when_on_progress(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Awal',
            'deskripsi' => 'Deskripsi awal.',
        ]);

        $this->actingAs($siswaUser)
            ->put("/siswa/jurnal/{$jurnal->id}", [
                'judul' => 'Update',
                'deskripsi' => 'Deskripsi updated.',
            ])
            ->assertRedirect();

        $this->assertSame('Update', $jurnal->fresh()->judul);
    }

    public function test_cannot_edit_jurnal_when_done_and_locked(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Lock Test',
            'deskripsi' => 'Akan di-done.',
        ]);
        $this->service->markDone($jurnal);

        // GET edit harus redirect dengan error
        $this->actingAs($siswaUser)
            ->get("/siswa/jurnal/{$jurnal->id}/edit")
            ->assertRedirect()
            ->assertSessionHas('error');

        // PUT edit harus gagal (exception → back with error)
        $response = $this->actingAs($siswaUser)
            ->put("/siswa/jurnal/{$jurnal->id}", [
                'judul' => 'Hack',
                'deskripsi' => 'Should fail.',
            ]);

        // Judul tidak berubah
        $this->assertSame('Lock Test', $jurnal->fresh()->judul);
    }

    public function test_can_mark_done(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Done Test',
            'deskripsi' => 'Test mark done.',
        ]);

        $this->actingAs($siswaUser)
            ->post("/siswa/jurnal/{$jurnal->id}/done")
            ->assertRedirect();

        $jurnal->refresh();
        $this->assertSame('done', $jurnal->status);
        $this->assertNotNull($jurnal->done_at);
        $this->assertNull($jurnal->unlocked_by);
    }

    public function test_can_hold_jurnal(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Hold Test',
            'deskripsi' => 'Test hold.',
        ]);

        $this->actingAs($siswaUser)
            ->post("/siswa/jurnal/{$jurnal->id}/hold")
            ->assertRedirect();

        $jurnal->refresh();
        $this->assertSame('hold', $jurnal->status);
        $this->assertNotNull($jurnal->hold_at);
    }

    public function test_reprogress_creates_new_child_jurnal(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Reprogress Test',
            'deskripsi' => 'Original deskripsi.',
            'kesulitan' => 'Original kesulitan.',
        ]);
        $this->service->hold($jurnal);

        // Reprogress
        $this->actingAs($siswaUser)
            ->post("/siswa/jurnal/{$jurnal->id}/reprogress")
            ->assertRedirect();

        $jurnal->refresh();
        $this->assertSame('reprogress', $jurnal->status);

        // Jurnal baru (child) harus dibuat
        $child = Jurnal::where('parent_id', $jurnal->id)->first();
        $this->assertNotNull($child);
        $this->assertSame('on_progress', $child->status);
        $this->assertSame('Reprogress Test', $child->judul);
        $this->assertSame($jurnal->siswa_id, $child->siswa_id);
    }

    public function test_reprogress_only_works_on_hold(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Reprogress Invalid',
            'deskripsi' => 'Belum di-hold.',
        ]);

        $this->actingAs($siswaUser)
            ->post("/siswa/jurnal/{$jurnal->id}/reprogress")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('on_progress', $jurnal->fresh()->status);
        $this->assertSame(0, Jurnal::where('parent_id', $jurnal->id)->count());
    }

    public function test_superadmin_can_unlock_done_jurnal(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Unlock Test',
            'deskripsi' => 'Akan di-unlock.',
        ]);
        $this->service->markDone($jurnal);

        $admin = User::where('role', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->post("/admin/jurnal/{$jurnal->id}/unlock")
            ->assertRedirect();

        $jurnal->refresh();
        $this->assertSame($admin->id, $jurnal->unlocked_by);
        $this->assertNotNull($jurnal->unlocked_at);

        // Siswa sekarang bisa edit
        $this->actingAs($siswaUser)
            ->get("/siswa/jurnal/{$jurnal->id}/edit")
            ->assertOk();
    }

    public function test_lock_resets_after_edit(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Lock Reset Test',
            'deskripsi' => 'Awal.',
        ]);
        $this->service->markDone($jurnal);

        $admin = User::where('role', 'superadmin')->firstOrFail();
        $this->service->unlock($jurnal, $admin->id);

        // Siswa edit
        $this->service->update($jurnal, [
            'judul' => 'Edited',
            'deskripsi' => 'Updated.',
        ]);

        // unlocked_by/at harus di-reset
        $this->assertNull($jurnal->fresh()->unlocked_by);
        $this->assertNull($jurnal->fresh()->unlocked_at);
        $this->assertSame('done', $jurnal->fresh()->status);
    }

    public function test_guru_can_only_see_own_school_jurnal(): void
    {
        $guru = User::where('role', 'guru')->firstOrFail();
        $guruRow = $guru->guru;

        // ID jurnal milik sekolah lain (kalau ada)
        $otherGuru = \App\Models\Guru::where('sekolah_id', '!=', $guruRow->sekolah_id)->first();
        if (!$otherGuru) {
            $this->markTestSkipped('Hanya ada 1 sekolah, tidak bisa test scope.');
        }

        $otherSiswa = PenempatanPkl::where('guru_id', $otherGuru->id)->first();
        if (!$otherSiswa) {
            $this->markTestSkipped('Tidak ada siswa di sekolah lain.');
        }

        $otherJurnal = Jurnal::where('siswa_id', $otherSiswa->siswa_id)->first();
        if (!$otherJurnal) {
            $this->markTestSkipped('Tidak ada jurnal siswa lain.');
        }

        // Guru seharusnya tidak bisa akses jurnal sekolah lain
        $this->actingAs($guru)
            ->get("/guru/jurnal/{$otherJurnal->id}")
            ->assertStatus(403);
    }

    public function test_pembimbing_can_only_see_bimbingan_jurnal(): void
    {
        $pb1 = User::where('role', 'pembimbing')->firstOrFail();

        // Cari pembimbing lain (jika ada)
        $otherPb = \App\Models\Pembimbing::where('id', '!=', $pb1->pembimbing->id)->first();
        if (!$otherPb) {
            $this->markTestSkipped('Hanya ada 1 pembimbing.');
        }

        $otherSiswa = PenempatanPkl::where('pembimbing_id', $otherPb->id)->first();
        if (!$otherSiswa) {
            $this->markTestSkipped('Tidak ada siswa bimbingan pembimbing lain.');
        }

        $otherJurnal = Jurnal::where('siswa_id', $otherSiswa->siswa_id)->first();
        if (!$otherJurnal) {
            $this->markTestSkipped('Tidak ada jurnal siswa lain.');
        }

        $this->actingAs($pb1)
            ->get("/pembimbing/jurnal/{$otherJurnal->id}")
            ->assertStatus(403);
    }

    public function test_status_log_records_every_transition(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $jurnal = $this->service->create($siswa, [
            'judul' => 'Status Log Test',
            'deskripsi' => 'Tracking.',
        ]);

        $this->service->markDone($jurnal);

        $logs = $jurnal->statusLog()->orderBy('id')->get();
        $this->assertCount(2, $logs);
        $this->assertNull($logs[0]->dari_status);
        $this->assertSame('on_progress', $logs[0]->ke_status);
        $this->assertSame('on_progress', $logs[1]->dari_status);
        $this->assertSame('done', $logs[1]->ke_status);
    }

    public function test_pulang_auto_holds_on_progress_jurnals(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $j1 = $this->service->create($siswa, ['judul' => 'J1', 'deskripsi' => 'A']);
        $j2 = $this->service->create($siswa, ['judul' => 'J2', 'deskripsi' => 'B']);

        $this->actingAs($siswaUser)
            ->postJson('/siswa/absen/pulang')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame('hold', $j1->fresh()->status);
        $this->assertSame('hold', $j2->fresh()->status);
    }

    public function test_validation_errors(): void
    {
        [$siswaUser, $siswa, $absensi] = $this->siswaWithAbsen();

        $countBefore = Jurnal::where('siswa_id', $siswa->id)->count();

        $response = $this->actingAs($siswaUser)
            ->post('/siswa/jurnal', [
                'judul' => '',
                'deskripsi' => 'Cuma deskripsi',
            ]);

        $response->assertSessionHasErrors(['judul']);
        $this->assertSame($countBefore, Jurnal::where('siswa_id', $siswa->id)->count());
    }

    public function test_guru_can_add_catatan_to_jurnal(): void
    {
        $guru = User::where('role', 'guru')->firstOrFail();
        $guruRow = $guru->guru;

        // Guru hanya bisa kirim pesan, bukan catatan jurnal
        // Test ini memastikan guru tidak salah akses endpoint catatan jurnal
        $jurnal = Jurnal::whereHas('siswa', fn($q) => $q->where('sekolah_id', $guruRow->sekolah_id))->first();

        $this->actingAs($guru)
            ->get("/pembimbing/jurnal/{$jurnal->id}/catatan/create")
            ->assertStatus(403);
    }

    public function test_pembimbing_can_add_catatan(): void
    {
        $pb = User::where('role', 'pembimbing')->firstOrFail();
        $jurnal = Jurnal::whereHas('penempatan', fn($q) => $q->where('pembimbing_id', $pb->pembimbing->id))->first();

        $this->actingAs($pb)
            ->get("/pembimbing/jurnal/{$jurnal->id}/catatan/create")
            ->assertOk();

        $response = $this->actingAs($pb)
            ->post("/pembimbing/jurnal/{$jurnal->id}/catatan", [
                'isi' => 'Bagus, terus kembangkan!',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('catatan', [
            'jurnal_id' => $jurnal->id,
            'pengirim_id' => $pb->id,
            'tipe' => 'catatan_jurnal',
            'isi' => 'Bagus, terus kembangkan!',
        ]);
    }
}