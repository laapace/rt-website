<?php

namespace App\Policies;

use App\Models\KartuKeluarga;
use App\Models\User;

class KartuKeluargaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function view(User $user, KartuKeluarga $kartuKeluarga): bool
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return true;
        }

        return $kartuKeluarga->id === $user->kartu_keluarga_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function update(User $user, KartuKeluarga $kartuKeluarga): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function delete(User $user, KartuKeluarga $kartuKeluarga): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }
}
