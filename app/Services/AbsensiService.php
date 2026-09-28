<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Pembimbing;
use App\Models\PengaturanAbsensi;
use App\Models\PenempatanPkl;
use App\Models\QrToken;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AbsensiService
{
    public const TOKEN_TTL_SECONDS = 30;

    public function __construct(private \App\Services\JurnalService $jurnalService)
    {
    }

    public function getOrCreateToken(Pembimbing $pembimbing): QrToken
    {
        $token = QrToken::where('pembimbing_id', $pembimbing->id)
            ->where('expired_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if ($token) {
            return $token;
        }

        return $this->regenerateToken($pembimbing);
    }

    public function regenerateToken(Pembimbing $pembimbing): QrToken
    {
        return QrToken::create([
            'pembimbing_id' => $pembimbing->id,
            'token' => Str::random(48),
            'expired_at' => now()->addSeconds(self::TOKEN_TTL_SECONDS),
        ]);
    }

    public function remainingSeconds(QrToken $token): int
    {
        return max(0, now()->diffInSeconds($token->expired_at, false));
    }

    public function isLate(PenempatanPkl $penempatan, Carbon $waktuMasuk): bool
    {
        $pengaturan = PengaturanAbsensi::forHari(
            $penempatan->perusahaan_id,
            (int) $waktuMasuk->dayOfWeek === 0 ? 7 : $waktuMasuk->dayOfWeek
        );

        if (! $pengaturan || $pengaturan->libur) {
            return false;
        }

        return $waktuMasuk->format('H:i:s') > $pengaturan->jam_masuk;
    }

    public function scanMasuk(string $tokenValue): array
    {
        $token = QrToken::where('token', $tokenValue)
            ->where('expired_at', '>', now())
            ->first();

        if (! $token) {
            return ['ok' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa.'];
        }

        $siswa = auth()->user()?->siswa;
        if (! $siswa) {
            return ['ok' => false, 'message' => 'Akun tidak terkait dengan siswa.'];
        }

        $penempatan = $siswa->penempatanAktif;
        if (! $penempatan) {
            return ['ok' => false, 'message' => 'Anda belum punya penempatan aktif.'];
        }

        if ($penempatan->pembimbing_id !== $token->pembimbing_id) {
            return ['ok' => false, 'message' => 'Token ini bukan dari pembimbing Anda.'];
        }

        $existing = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', today())
            ->first();

        if ($existing) {
            return ['ok' => false, 'message' => 'Anda sudah absen hari ini.'];
        }

        $now = now();
        $status = $this->isLate($penempatan, $now) ? 'terlambat' : 'hadir';

        $absensi = Absensi::create([
            'siswa_id'      => $siswa->id,
            'penempatan_id' => $penempatan->id,
            'qr_token_id'   => $token->id,
            'tanggal'       => today(),
            'jam_masuk'     => $now,
            'status'        => $status,
        ]);

        return ['ok' => true, 'absensi' => $absensi, 'message' => 'Absen masuk berhasil.'];
    }

    public function pulang(): array
    {
        $siswa = auth()->user()?->siswa;
        if (! $siswa) {
            return ['ok' => false, 'message' => 'Akun tidak terkait dengan siswa.'];
        }

        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', today())
            ->first();

        if (! $absensi) {
            return ['ok' => false, 'message' => 'Anda belum absen masuk hari ini.'];
        }

        if ($absensi->jam_pulang) {
            return ['ok' => false, 'message' => 'Anda sudah absen pulang.'];
        }

        $countHold = $this->jurnalService->autoHoldOnProgress($siswa);

        $absensi->update(['jam_pulang' => now()]);

        $msg = 'Absen pulang berhasil.';
        if ($countHold > 0) {
            $msg .= " {$countHold} jurnal on_progress di-hold otomatis.";
        }

        return ['ok' => true, 'absensi' => $absensi->fresh(), 'message' => $msg, 'held_count' => $countHold];
    }

    public function ajukanIzin(string $jenis, ?string $keterangan, ?string $lampiranPath = null): array
    {
        $siswa = auth()->user()?->siswa;
        if (! $siswa) {
            return ['ok' => false, 'message' => 'Akun tidak terkait dengan siswa.'];
        }

        $penempatan = $siswa->penempatanAktif;
        if (! $penempatan) {
            return ['ok' => false, 'message' => 'Anda belum punya penempatan aktif.'];
        }

        if (! in_array($jenis, ['izin', 'sakit'])) {
            return ['ok' => false, 'message' => 'Jenis pengajuan tidak valid.'];
        }

        $existing = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', today())
            ->first();

        if ($existing) {
            return ['ok' => false, 'message' => 'Sudah ada absensi untuk hari ini.'];
        }

        $absensi = Absensi::create([
            'siswa_id'      => $siswa->id,
            'penempatan_id' => $penempatan->id,
            'qr_token_id'   => null,
            'tanggal'       => today(),
            'jam_masuk'     => now(),
            'status'        => $jenis,
            'keterangan'    => $keterangan,
            'lampiran'      => $lampiranPath,
        ]);

        return ['ok' => true, 'absensi' => $absensi, 'message' => 'Pengajuan ' . ucfirst($jenis) . ' berhasil dicatat.'];
    }
}