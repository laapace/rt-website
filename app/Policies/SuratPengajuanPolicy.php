<?php

namespace App\Policies;

use App\Models\SuratPengajuan;
use App\Models\User;

class SuratPengajuanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SuratPengajuan $surat): bool
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return true;
        }

        return $surat->warga_id === $user->warga_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Memproses (diproses / setujui / tolak): Sekretaris, Ketua, Admin.
     */
    public function update(User $user, SuratPengajuan $surat): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function delete(User $user, SuratPengajuan $surat): bool
    {
        return $user->hasRole('admin');
    }
}
