<?php

namespace App\Policies;

use App\Models\PenempatanPkl;
use App\Models\Proyek;
use App\Models\User;

class ProyekPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Proyek $proyek): bool
    {
        if ($user->role === 'superadmin') return true;

        if ($user->role === 'siswa') {
            return $proyek->siswa_id === $user->siswa?->id;
        }

        if ($user->role === 'pembimbing') {
            return PenempatanPkl::where('pembimbing_id', $user->pembimbing->id)
                ->where('siswa_id', $proyek->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        if ($user->role === 'guru') {
            return PenempatanPkl::where('guru_id', $user->guru->id)
                ->where('siswa_id', $proyek->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'pembimbing'], true);
    }

    public function update(User $user, Proyek $proyek): bool
    {
        if ($user->role === 'superadmin') return true;

        if ($user->role === 'pembimbing') {
            return PenempatanPkl::where('pembimbing_id', $user->pembimbing->id)
                ->where('siswa_id', $proyek->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        return false;
    }

    public function delete(User $user, Proyek $proyek): bool
    {
        return $this->update($user, $proyek);
    }

    public function addProgress(User $user, Proyek $proyek): bool
    {
        if ($user->role === 'superadmin') return true;

        if ($user->role === 'siswa') {
            return $proyek->siswa_id === $user->siswa?->id && !in_array($proyek->status, ['selesai', 'batal']);
        }

        return $this->update($user, $proyek);
    }

    public function setStatus(User $user, Proyek $proyek): bool
    {
        return $this->update($user, $proyek);
    }
}