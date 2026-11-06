<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanWarga extends Model
{
    protected $table = 'laporan_warga';

    use HasUuids;

    protected $fillable = [
        'warga_id',
        'dilaporkan_oleh_user_id',
        'kategori',
        'judul',
        'deskripsi',
        'foto_path',
        'status',
        'komentar_pengurus',
        'ditangani_oleh',
    ];

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function dilaporkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilaporkan_oleh_user_id');
    }

    public function ditanganiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return $query;
        }

        return $query->where('warga_id', $user->warga_id);
    }
}
