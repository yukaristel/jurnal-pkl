<?php

namespace App\Services;

use App\Models\PenempatanPkl;
use App\Models\Proyek;
use App\Models\ProyekProgress;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class ProyekService
{
    public const STATUS = ['belum_mulai', 'berjalan', 'selesai', 'batal'];

    public function create(Siswa $siswa, array $data, int $pemberiByUserId): Proyek
    {
        $penempatan = $siswa->penempatanAktif;
        if (! $penempatan) {
            throw new \RuntimeException('Siswa belum punya penempatan aktif.');
        }

        return DB::transaction(function () use ($siswa, $data, $pemberiByUserId, $penempatan) {
            $proyek = Proyek::create([
                'siswa_id'        => $siswa->id,
                'penempatan_id'   => $penempatan->id,
                'judul'           => $data['judul'],
                'deskripsi'       => $data['deskripsi'] ?? null,
                'diberikan_oleh'  => $pemberiByUserId,
                'tanggal_mulai'   => $data['tanggal_mulai'],
                'target_selesai'  => $data['target_selesai'],
                'status'          => 'belum_mulai',
            ]);

            if (isset($data['progress_awal'])) {
                $this->addProgress($proyek, [
                    'keterangan' => $data['progress_awal']['keterangan'] ?? 'Proyek dibuat',
                    'persen'     => $data['progress_awal']['persen'] ?? 0,
                ], $pemberiByUserId);
            }

            return $proyek;
        });
    }

    public function update(Proyek $proyek, array $data): Proyek
    {
        $proyek->update([
            'judul'          => $data['judul'],
            'deskripsi'      => $data['deskripsi'] ?? null,
            'tanggal_mulai'  => $data['tanggal_mulai'],
            'target_selesai' => $data['target_selesai'],
        ]);

        return $proyek->fresh();
    }

    public function addProgress(Proyek $proyek, array $data, int $userId): ProyekProgress
    {
        $progress = ProyekProgress::create([
            'proyek_id'   => $proyek->id,
            'keterangan'  => $data['keterangan'],
            'persen'      => $data['persen'],
            'lampiran'    => $data['lampiran'] ?? null,
        ]);

        $proyek->refresh();

        if ($proyek->status === 'belum_mulai' && $progress->persen > 0) {
            $proyek->update(['status' => 'berjalan']);
        }

        if ($progress->persen >= 100 && $proyek->status !== 'selesai') {
            $proyek->update([
                'status'         => 'selesai',
                'tanggal_selesai'=> now()->toDateString(),
            ]);
        }

        return $progress;
    }

    public function setStatus(Proyek $proyek, string $status, ?string $hasil = null): Proyek
    {
        if (! in_array($status, self::STATUS)) {
            throw new \RuntimeException('Status tidak valid.');
        }

        $update = ['status' => $status];
        if ($status === 'selesai') {
            $update['tanggal_selesai'] = now()->toDateString();
        }
        if ($hasil !== null) {
            $update['hasil'] = $hasil;
        }

        $proyek->update($update);
        return $proyek->fresh();
    }
}