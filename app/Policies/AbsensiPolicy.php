<?php

namespace App\Policies;

use App\Models\Absensi;
use App\Models\PenempatanPkl;
use App\Models\User;

class AbsensiPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Absensi $absensi): bool
    {
        if ($user->role === 'superadmin') return true;

        if ($user->role === 'siswa') {
            return $absensi->siswa_id === $user->siswa?->id;
        }

        if ($user->role === 'pembimbing') {
            return PenempatanPkl::where('pembimbing_id', $user->pembimbing->id)
                ->where('siswa_id', $absensi->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        if ($user->role === 'guru') {
            return PenempatanPkl::where('guru_id', $user->guru->id)
                ->where('siswa_id', $absensi->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        return false;
    }

    public function scan(User $user): bool
    {
        return $user->role === 'siswa' && $user->siswa?->penempatanAktif;
    }

    public function pulang(User $user, Absensi $absensi): bool
    {
        return $user->role === 'siswa' && $absensi->siswa_id === $user->siswa?->id;
    }

    public function ajukanIzin(User $user): bool
    {
        return $user->role === 'siswa' && $user->siswa?->penempatanAktif;
    }
}