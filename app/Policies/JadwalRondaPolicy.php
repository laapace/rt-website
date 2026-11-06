<?php

namespace App\Policies;

use App\Models\JadwalRonda;
use App\Models\User;

class JadwalRondaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function view(User $user, JadwalRonda $jadwalRonda): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function update(User $user, JadwalRonda $jadwalRonda): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function delete(User $user, JadwalRonda $jadwalRonda): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }
}
