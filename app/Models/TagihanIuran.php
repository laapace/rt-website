<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanIuran extends Model
{
    protected $table = 'tagihan_iuran';

    use HasUuids;

    protected $fillable = [
        'kartu_keluarga_id',
        'tarif_id',
        'periode',
        'nominal',
        'jatuh_tempo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jatuh_tempo' => 'date',
        ];
    }

    /**
     * Status untuk tampilan: tagihan belum_bayar yang sudah lewat
     * jatuh tempo tampil sebagai 'terlambat' (dihitung di aplikasi,
     * bukan diubah di database oleh cron).
     */
    public function getStatusTampilAttribute(): string
    {
        if ($this->status === 'belum_bayar' && $this->jatuh_tempo && $this->jatuh_tempo->isPast()) {
            return 'terlambat';
        }

        return $this->status;
    }

    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class);
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(TarifIuran::class, 'tarif_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    /** Pembayaran terbaru (untuk view: pembayaran adalah HasMany). */
    public function getPembayaranTerakhirAttribute(): ?Pembayaran
    {
        return $this->pembayaran()->latest()->first();
    }

    public function getTanggalBayarAttribute(): mixed
    {
        return $this->pembayaran_terakhir?->tanggal_bayar;
    }

    public function getBuktiPathAttribute(): ?string
    {
        return $this->pembayaran_terakhir?->bukti_path;
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return $query;
        }

        return $query->where('kartu_keluarga_id', $user->kartu_keluarga_id);
    }
}
