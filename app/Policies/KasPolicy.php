<?php

namespace App\Policies;

use App\Models\Kas;
use App\Models\User;

class KasPolicy
{
    /**
     * Warga TIDAK melihat tabel kas langsung — hanya ringkasan lewat
     * view v_kas_ringkas. Daftar kas hanya untuk pengurus.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function view(User $user, Kas $kas): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function update(User $user, Kas $kas): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function delete(User $user, Kas $kas): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }
}
