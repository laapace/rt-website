<?php

namespace App\Policies;

use App\Models\TagihanIuran;
use App\Models\User;

class TagihanIuranPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TagihanIuran $tagihan): bool
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return true;
        }

        return $tagihan->kartu_keluarga_id === $user->kartu_keluarga_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function update(User $user, TagihanIuran $tagihan): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function delete(User $user, TagihanIuran $tagihan): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }
}
