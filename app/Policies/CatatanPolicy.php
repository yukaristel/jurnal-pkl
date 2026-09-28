<?php

namespace App\Policies;

use App\Models\Catatan;
use App\Models\PenempatanPkl;
use App\Models\User;

class CatatanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Catatan $catatan): bool
    {
        if ($user->role === 'superadmin') return true;

        if ($user->role === 'siswa') {
            return $catatan->siswa_id === $user->siswa?->id;
        }

        if ($user->role === 'pembimbing') {
            if ($catatan->tipe !== 'catatan_jurnal') return false;
            return PenempatanPkl::where('pembimbing_id', $user->pembimbing->id)
                ->where('siswa_id', $catatan->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        if ($user->role === 'guru') {
            if ($catatan->tipe !== 'pesan') return false;
            return $catatan->pengirim_id === $user->id
                || PenempatanPkl::where('guru_id', $user->guru->id)
                    ->where('siswa_id', $catatan->siswa_id)
                    ->where('status', 'aktif')
                    ->exists();
        }

        return false;
    }

    public function createCatatanJurnal(User $user): bool
    {
        return $user->role === 'pembimbing';
    }

    public function createPesan(User $user): bool
    {
        return $user->role === 'guru';
    }
}