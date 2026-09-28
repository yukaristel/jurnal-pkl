<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\JurnalStatusLog;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class JurnalService
{
    public const STATUS = ['on_progress', 'done', 'hold', 'reprogress'];

    public function canCreate(Siswa $siswa): bool
    {
        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', today())
            ->first();

        if (! $absensi) {
            return false;
        }

        return ! in_array($absensi->status, ['izin', 'sakit']);
    }

    public function create(Siswa $siswa, array $data, ?int $userId = null): Jurnal
    {
        if (! $this->canCreate($siswa)) {
            throw new \RuntimeException('Anda belum absen hari ini atau status absensi tidak mengizinkan.');
        }

        $absensi = Absensi::where('siswa_id', $siswa->id)
            ->where('tanggal', today())
            ->first();

        $userId ??= $siswa->user_id;
        $userId ??= auth()->id();

        return DB::transaction(function () use ($siswa, $data, $absensi, $userId) {
            $jurnal = Jurnal::create([
                'siswa_id'      => $siswa->id,
                'penempatan_id' => $absensi->penempatan_id,
                'absensi_id'    => $absensi->id,
                'tanggal'       => today(),
                'judul'         => $data['judul'],
                'deskripsi'     => $data['deskripsi'],
                'kesulitan'     => $data['kesulitan'] ?? null,
                'status'        => 'on_progress',
            ]);

            JurnalStatusLog::create([
                'jurnal_id'   => $jurnal->id,
                'dari_status' => null,
                'ke_status'   => 'on_progress',
                'user_id'     => $userId,
                'waktu'       => now(),
            ]);

            return $jurnal;
        });
    }

    public function update(Jurnal $jurnal, array $data, ?int $unlockByUserId = null): Jurnal
    {
        $isLocked = $jurnal->status === 'done' && $jurnal->unlocked_by === null && $unlockByUserId === null;

        if ($isLocked) {
            throw new \RuntimeException('Jurnal yang sudah selesai dikunci. Minta superadmin membuka kunci.');
        }

        return DB::transaction(function () use ($jurnal, $data) {
            $jurnal->update([
                'judul'     => $data['judul'],
                'deskripsi' => $data['deskripsi'],
                'kesulitan' => $data['kesulitan'] ?? null,
                'unlocked_by' => null,
                'unlocked_at' => null,
            ]);

            return $jurnal->fresh();
        });
    }

    public function markDone(Jurnal $jurnal, ?int $userId = null): Jurnal
    {
        return $this->changeStatus($jurnal, 'done', ['done_at' => now()], $userId);
    }

    public function hold(Jurnal $jurnal, ?int $userId = null): Jurnal
    {
        return $this->changeStatus($jurnal, 'hold', ['hold_at' => now()], $userId);
    }

    public function reprogress(Jurnal $jurnal, ?int $userId = null): Jurnal
    {
        if ($jurnal->status !== 'hold') {
            throw new \RuntimeException('Hanya jurnal berstatus hold yang bisa dibuka kembali.');
        }

        $userId ??= auth()->id();
        $userId ??= \App\Models\Siswa::find($jurnal->siswa_id)?->user_id;

        return DB::transaction(function () use ($jurnal, $userId) {
            $this->changeStatus($jurnal, 'reprogress', [], $userId);

            $absensi = Absensi::where('siswa_id', $jurnal->siswa_id)
                ->where('tanggal', today())
                ->first();

            $new = Jurnal::create([
                'siswa_id'      => $jurnal->siswa_id,
                'penempatan_id' => $jurnal->penempatan_id,
                'absensi_id'    => $absensi?->id,
                'parent_id'     => $jurnal->id,
                'tanggal'       => today(),
                'judul'         => $jurnal->judul,
                'deskripsi'     => $jurnal->deskripsi,
                'kesulitan'     => $jurnal->kesulitan,
                'status'        => 'on_progress',
            ]);

            JurnalStatusLog::create([
                'jurnal_id'   => $new->id,
                'dari_status' => null,
                'ke_status'   => 'on_progress',
                'user_id'     => $userId,
                'waktu'       => now(),
            ]);

            return $new;
        });
    }

    public function unlock(Jurnal $jurnal, int $adminUserId): Jurnal
    {
        return DB::transaction(function () use ($jurnal, $adminUserId) {
            $jurnal->update([
                'unlocked_by' => $adminUserId,
                'unlocked_at' => now(),
            ]);

            JurnalStatusLog::create([
                'jurnal_id'   => $jurnal->id,
                'dari_status' => $jurnal->status,
                'ke_status'   => $jurnal->status,
                'user_id'     => $adminUserId,
                'waktu'       => now(),
            ]);

            return $jurnal->fresh();
        });
    }

    public function autoHoldOnProgress(Siswa $siswa, ?int $systemUserId = null): int
    {
        $systemUserId ??= auth()->id();
        $systemUserId ??= \App\Models\User::where('role', 'superadmin')->value('id');

        return DB::transaction(function () use ($siswa, $systemUserId) {
            $rows = Jurnal::where('siswa_id', $siswa->id)
                ->where('tanggal', today())
                ->where('status', 'on_progress')
                ->get();

            foreach ($rows as $row) {
                $row->update(['status' => 'hold', 'hold_at' => now()]);
                JurnalStatusLog::create([
                    'jurnal_id'   => $row->id,
                    'dari_status' => 'on_progress',
                    'ke_status'   => 'hold',
                    'user_id'     => $systemUserId,
                    'waktu'       => now(),
                ]);
            }

            return $rows->count();
        });
    }

    private function changeStatus(Jurnal $jurnal, string $to, array $extra = [], ?int $userId = null): Jurnal
    {
        $userId ??= auth()->id();
        $userId ??= \App\Models\Siswa::find($jurnal->siswa_id)?->user_id;

        return DB::transaction(function () use ($jurnal, $to, $extra, $userId) {
            $from = $jurnal->status;
            $jurnal->update(array_merge(['status' => $to], $extra));

            JurnalStatusLog::create([
                'jurnal_id'   => $jurnal->id,
                'dari_status' => $from,
                'ke_status'   => $to,
                'user_id'     => $userId,
                'waktu'       => now(),
            ]);

            return $jurnal->fresh();
        });
    }
}