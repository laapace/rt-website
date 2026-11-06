<?php

namespace App\Policies;

use App\Models\LaporanWarga;
use App\Models\User;

class LaporanWargaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LaporanWarga $laporan): bool
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return true;
        }

        return $laporan->warga_id === $user->warga_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Memproses laporan: Sekretaris, Ketua, Admin.
     */
    public function update(User $user, LaporanWarga $laporan): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function delete(User $user, LaporanWarga $laporan): bool
    {
        return $user->hasRole('admin');
    }
}
