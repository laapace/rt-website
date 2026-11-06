<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return true;
        }

        return $pembayaran->tagihan->kartu_keluarga_id === $user->kartu_keluarga_id;
    }

    /**
     * Warga membuat pembayaran hanya untuk tagihan KK-nya sendiri
     * (kepemilikan tagihan dicek di controller/service).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Verifikasi (setuju/tolak) hanya Bendahara (dan Admin).
     */
    public function update(User $user, Pembayaran $pembayaran): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }

    public function delete(User $user, Pembayaran $pembayaran): bool
    {
        return $user->hasRole('bendahara', 'admin');
    }
}
