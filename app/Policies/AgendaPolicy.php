<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function view(User $user, Agenda $agenda): bool
    {
        return $user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function update(User $user, Agenda $agenda): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        return $user->hasRole('sekretaris', 'ketua', 'admin');
    }
}
