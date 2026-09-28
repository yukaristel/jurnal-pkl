<?php

namespace App\Policies;

use App\Models\PenempatanPkl;
use App\Models\User;

class PenempatanPolicy
{
    public function manage(User $user): bool
    {
        return $user->role === 'superadmin';
    }

    public function view(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'guru', 'pembimbing'], true);
    }
}