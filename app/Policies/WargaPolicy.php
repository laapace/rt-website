<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warga;

class WargaPolicy
{
    /**
     * Pengurus (sekretaris, admin) dan warga (daftar dibatasi scope)
     * boleh melihat daftar; tamu tidak.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Warga $warga): bool
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return true;
        }

        return $warga->kartu_keluarga_id === $user->kartu_keluarga_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('sekretaris', 'admin');
    }

    public function update(User $user, Warga $warga): bool
    {
        return $user->hasRole('sekretaris', 'admin');
    }

    public function delete(User $user, Warga $warga): bool
    {
        return $user->hasRole('sekretaris', 'admin');
    }
}
