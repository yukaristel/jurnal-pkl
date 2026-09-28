<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\PenempatanPkl;
use App\Models\Proyek;
use App\Models\Siswa;

class LaporanService
{
    public function getSiswaScope(string $role, $user): \Illuminate\Support\Collection
    {
        return match ($role) {
            'superadmin' => Siswa::with('user', 'sekolah')->orderBy('nis')->get(),
            'guru' => Siswa::with('user', 'sekolah')
                ->whereIn('id', PenempatanPkl::where('guru_id', $user->guru->id)->where('status', 'aktif')->pluck('siswa_id'))
                ->orderBy('nis')->get(),
            'pembimbing' => Siswa::with('user')
                ->whereIn('id', PenempatanPkl::where('pembimbing_id', $user->pembimbing->id)->where('status', 'aktif')->pluck('siswa_id'))
                ->orderBy('nis')->get(),
            'siswa' => collect([$user->siswa]),
            default => collect(),
        };
    }

    public function dataKehadiran(array $filters): array
    {
        $query = Absensi::with(['siswa.user', 'siswa.sekolah', 'penempatan.perusahaan']);

        if (! empty($filters['siswa_id'])) {
            $query->where('siswa_id', $filters['siswa_id']);
        }
        if (! empty($filters['dari']) && ! empty($filters['sampai'])) {
            $query->whereBetween('tanggal', [$filters['dari'], $filters['sampai']]);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['perusahaan_id'])) {
            $query->whereHas('penempatan', fn($q) => $q->where('perusahaan_id', $filters['perusahaan_id']));
        }

        $rows = $query->orderBy('tanggal')->orderBy('siswa_id')->get();

        return [
            'rows' => $rows,
            'summary' => [
                'total' => $rows->count(),
                'hadir' => $rows->whereIn('status', ['hadir', 'terlambat'])->count(),
                'terlambat' => $rows->where('status', 'terlambat')->count(),
                'izin' => $rows->where('status', 'izin')->count(),
                'sakit' => $rows->where('status', 'sakit')->count(),
                'alpa' => $rows->where('status', 'alpa')->count(),
            ],
        ];
    }

    public function dataJurnal(array $filters): array
    {
        $query = Jurnal::with(['siswa.user', 'siswa.sekolah', 'catatan.pengirim']);

        if (! empty($filters['siswa_id'])) {
            $query->where('siswa_id', $filters['siswa_id']);
        }
        if (! empty($filters['dari']) && ! empty($filters['sampai'])) {
            $query->whereBetween('tanggal', [$filters['dari'], $filters['sampai']]);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $rows = $query->orderBy('tanggal')->orderBy('siswa_id')->get();

        return [
            'rows' => $rows,
            'summary' => [
                'total' => $rows->count(),
                'on_progress' => $rows->where('status', 'on_progress')->count(),
                'done' => $rows->where('status', 'done')->count(),
                'hold' => $rows->where('status', 'hold')->count(),
                'reprogress' => $rows->where('status', 'reprogress')->count(),
            ],
        ];
    }

    public function dataProyek(array $filters): array
    {
        $query = Proyek::with(['siswa.user', 'siswa.sekolah', 'progress']);

        if (! empty($filters['siswa_id'])) {
            $query->where('siswa_id', $filters['siswa_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $rows = $query->orderByDesc('id')->get();

        return [
            'rows' => $rows,
            'summary' => [
                'total' => $rows->count(),
                'belum_mulai' => $rows->where('status', 'belum_mulai')->count(),
                'berjalan' => $rows->where('status', 'berjalan')->count(),
                'selesai' => $rows->where('status', 'selesai')->count(),
                'batal' => $rows->where('status', 'batal')->count(),
            ],
        ];
    }
}