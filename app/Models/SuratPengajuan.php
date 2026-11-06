<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPengajuan extends Model
{
    protected $table = 'surat_pengajuan';

    use HasUuids;

    protected $fillable = [
        'warga_id',
        'diajukan_oleh_user_id',
        'jenis',
        'keperluan',
        'status',
        'nomor_surat',
        'alasan_penolakan',
        'diproses_oleh',
        'diproses_pada',
        'data_surat',
    ];

    protected function casts(): array
    {
        return [
            'data_surat' => 'array',
            'diproses_pada' => 'datetime',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh_user_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return $query;
        }

        return $query->where('warga_id', $user->warga_id);
    }
}
