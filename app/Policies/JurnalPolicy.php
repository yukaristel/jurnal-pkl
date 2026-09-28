<?php

namespace App\Policies;

use App\Models\Jurnal;
use App\Models\PenempatanPkl;
use App\Models\User;

class JurnalPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'guru', 'pembimbing', 'siswa'], true);
    }

    public function view(User $user, Jurnal $jurnal): bool
    {
        if ($user->role === 'superadmin') return true;

        if ($user->role === 'siswa') {
            return $jurnal->siswa_id === $user->siswa?->id;
        }

        if ($user->role === 'pembimbing') {
            return PenempatanPkl::where('pembimbing_id', $user->pembimbing->id)
                ->where('siswa_id', $jurnal->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        if ($user->role === 'guru') {
            return PenempatanPkl::where('guru_id', $user->guru->id)
                ->where('siswa_id', $jurnal->siswa_id)
                ->where('status', 'aktif')
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'siswa';
    }

    public function update(User $user, Jurnal $jurnal): bool
    {
        if ($user->role !== 'siswa') return false;

        if ($jurnal->siswa_id !== $user->siswa?->id) return false;

        if ($jurnal->status === 'done' && $jurnal->unlocked_by === null) return false;

        return true;
    }

    public function delete(User $user, Jurnal $jurnal): bool
    {
        return $user->role === 'superadmin'
            || ($user->role === 'siswa' && $jurnal->siswa_id === $user->siswa?->id);
    }

    public function changeStatus(User $user, Jurnal $jurnal): bool
    {
        return $user->role === 'siswa' && $jurnal->siswa_id === $user->siswa?->id;
    }

    public function unlock(User $user, Jurnal $jurnal): bool
    {
        return $user->role === 'superadmin' && $jurnal->status === 'done';
    }
}