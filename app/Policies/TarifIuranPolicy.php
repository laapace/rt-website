<?php

namespace App\Policies;

use App\Models\TarifIuran;
use App\Models\User;

class TarifIuranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function view(User $user, TarifIuran $tarifIuran): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function update(User $user, TarifIuran $tarifIuran): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function delete(User $user, TarifIuran $tarifIuran): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }
}
