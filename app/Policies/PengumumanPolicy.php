<?php

namespace App\Policies;

use App\Models\Pengumuman;
use App\Models\User;

class PengumumanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function view(User $user, Pengumuman $pengumuman): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function update(User $user, Pengumuman $pengumuman): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function delete(User $user, Pengumuman $pengumuman): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }
}
