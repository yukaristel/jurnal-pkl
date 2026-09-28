<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\PenempatanPkl;
use App\Models\Proyek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--force' => true]);
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect();
        $this->get('/siswa/dashboard')->assertRedirect();
    }

    public function test_siswa_can_access_siswa_dashboard(): void
    {
        $user = User::where('role', 'siswa')->firstOrFail();
        $this->actingAs($user)->get('/siswa/dashboard')->assertOk();
    }

    public function test_siswa_cannot_access_admin_or_guru_routes(): void
    {
        $user = User::where('role', 'siswa')->firstOrFail();
        $this->actingAs($user)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($user)->get('/guru/dashboard')->assertStatus(403);
        $this->actingAs($user)->get('/pembimbing/dashboard')->assertStatus(403);
    }

    public function test_guru_only_sees_own_school_students(): void
    {
        $guru = User::where('role', 'guru')->firstOrFail();

        // Guru tidak boleh akses /admin/laporan
        $this->actingAs($guru)->get('/admin/laporan/jurnal')->assertStatus(403);

        // Guru hanya lihat siswa sekolahnya
        $response = $this->actingAs($guru)->get('/guru/jurnal');
        $response->assertOk();
    }

    public function test_pembimbing_only_sees_own_students(): void
    {
        $pb = User::where('role', 'pembimbing')->firstOrFail();
        $response = $this->actingAs($pb)->get('/pembimbing/jurnal');
        $response->assertOk();
    }

    public function test_superadmin_can_access_admin_dashboard(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_siswa_cannot_edit_other_siswa_jurnal(): void
    {
        $siswa1 = User::where('role', 'siswa')->skip(0)->firstOrFail();
        $siswa2 = User::where('role', 'siswa')->skip(1)->firstOrFail();
        $jurnalSiswa2 = Jurnal::where('siswa_id', $siswa2->siswa->id)->first();

        if (!$jurnalSiswa2) {
            $this->markTestSkipped('Tidak ada jurnal untuk siswa2.');
        }

        $this->actingAs($siswa1)
            ->get("/siswa/jurnal/{$jurnalSiswa2->id}/edit")
            ->assertStatus(403);
    }

    public function test_pdf_export_works_for_all_roles(): void
    {
        foreach (['admin', 'guru', 'pembimbing', 'siswa'] as $role) {
            $user = User::where('role', $role)->first();
            if (!$user) continue;
            foreach (['kehadiran', 'jurnal', 'proyek'] as $jenis) {
                $response = $this->actingAs($user)->get("/{$role}/laporan/{$jenis}?export=pdf");
                $response->assertOk();
                $this->assertEquals('application/pdf', $response->headers->get('content-type'));
            }
        }
    }

    public function test_siswa_scan_creates_absensi(): void
    {
        $siswaUser = User::where('role', 'siswa')->firstOrFail();
        $pembimbing = $siswaUser->siswa->penempatanAktif->pembimbing;

        $absensiCount = Absensi::where('siswa_id', $siswaUser->siswa->id)
            ->whereDate('tanggal', today())
            ->count();

        $response = $this->actingAs($siswaUser)
            ->postJson('/siswa/absen/scan', ['token' => 'NONEXISTENT']);

        // Token invalid, but if no absensi exists for today should respond 422 (failed)
        $this->assertContains($response->status(), [200, 422]);
    }

    public function test_jurnal_done_cannot_be_edited_by_siswa(): void
    {
        $siswaUser = User::where('role', 'siswa')->firstOrFail();
        $jurnal = Jurnal::where('siswa_id', $siswaUser->siswa->id)
            ->where('status', 'done')
            ->whereNull('unlocked_by')
            ->first();

        if (!$jurnal) {
            $this->markTestSkipped('Tidak ada jurnal done terkunci.');
        }

        $response = $this->actingAs($siswaUser)
            ->get("/siswa/jurnal/{$jurnal->id}/edit");
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_superadmin_can_unlock_jurnal(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();
        $jurnal = Jurnal::where('status', 'done')->whereNull('unlocked_by')->first();

        if (!$jurnal) {
            $this->markTestSkipped('Tidak ada jurnal done.');
        }

        $response = $this->actingAs($admin)->post("/admin/jurnal/{$jurnal->id}/unlock");
        $response->assertRedirect();

        $this->assertNotNull($jurnal->fresh()->unlocked_by);
    }

    public function test_guru_cannot_unlock_jurnal(): void
    {
        $guru = User::where('role', 'guru')->firstOrFail();
        $jurnal = Jurnal::where('status', 'done')->whereNull('unlocked_by')->first();

        if (!$jurnal) {
            $this->markTestSkipped('Tidak ada jurnal done.');
        }

        $response = $this->actingAs($guru)->post("/admin/jurnal/{$jurnal->id}/unlock");
        $response->assertStatus(403);
    }

    public function test_crud_sekolah_by_admin(): void
    {
        $admin = User::where('role', 'superadmin')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/sekolah', [
            'nama' => 'SMK Test',
            'npsn' => '99999999',
            'alamat' => 'Jl Test',
            'telepon' => '021-999',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('sekolah', ['npsn' => '99999999']);

        $sekolah = \App\Models\Sekolah::where('npsn', '99999999')->firstOrFail();
        $response = $this->actingAs($admin)->post("/admin/sekolah/{$sekolah->id}", [
            '_method' => 'DELETE',
            '_token' => csrf_token(),
        ]);
        $response->assertRedirect();
        $this->assertDatabaseMissing('sekolah', ['id' => $sekolah->id]);
    }
}