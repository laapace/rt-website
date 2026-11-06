<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KartuKeluarga extends Model
{
    protected $table = 'kartu_keluarga';

    use HasUuids, SoftDeletes;

    protected $fillable = [
        'no_kk',
        'alamat',
        'blok',
        'rt',
        'rw',
        'kepala_keluarga_id',
        'status_tinggal',
    ];

    // $kk->kepala_keluarga dipakai view sebagai NAMA kepala keluarga (string).
    public function getKepalaKeluargaAttribute(): ?string
    {
        return $this->kepalaKeluarga()->first()?->nama_lengkap;
    }

    public function warga(): HasMany
    {
        return $this->hasMany(Warga::class);
    }

    public function kepalaKeluarga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'kepala_keluarga_id');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanIuran::class);
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return $query;
        }

        return $query->where('id', $user->kartu_keluarga_id);
    }
}
