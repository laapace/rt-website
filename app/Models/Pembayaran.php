<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    use HasUuids;

    protected $fillable = [
        'tagihan_id',
        'dibayar_oleh_user_id',
        'nominal_dibayar',
        'metode',
        'bukti_path',
        'tanggal_bayar',
        'status',
        'alasan_penolakan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'date',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanIuran::class, 'tagihan_id');
    }

    public function dibayarOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibayar_oleh_user_id');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function kas(): HasOne
    {
        return $this->hasOne(Kas::class, 'pembayaran_id');
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return $query;
        }

        return $query->whereHas('tagihan', function (Builder $q) use ($user) {
            $q->where('kartu_keluarga_id', $user->kartu_keluarga_id);
        });
    }
}
