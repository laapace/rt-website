<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warga extends Model
{
    protected $table = 'warga';

    use HasUuids, SoftDeletes;

    protected $fillable = [
        'kartu_keluarga_id',
        'nik',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'hubungan_keluarga',
        'pekerjaan',
        'status_tinggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * NIK tersamar untuk peran Warga: **** + 4 digit terakhir.
     * Blade Warga memakai accessor ini, bukan kolom nik mentah.
     */
    // Alias ramah-view: Blade memakai $warga->nama / ->hubungan
    public function getNamaAttribute(): ?string
    {
        return $this->attributes['nama_lengkap'] ?? null;
    }

    public function getHubunganAttribute(): ?string
    {
        return $this->attributes['hubungan_keluarga'] ?? null;
    }

    public function getNikTersamarAttribute(): string
    {
        return '****'.substr($this->nik, -4);
    }

    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class);
    }

    public function suratPengajuan(): HasMany
    {
        return $this->hasMany(SuratPengajuan::class);
    }

    public function laporanWarga(): HasMany
    {
        return $this->hasMany(LaporanWarga::class);
    }

    public function jadwalRonda(): BelongsToMany
    {
        return $this->belongsToMany(JadwalRonda::class, 'ronda_petugas')
            ->withTimestamps();
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('bendahara', 'ketua', 'sekretaris', 'admin')) {
            return $query;
        }

        return $query->where('kartu_keluarga_id', $user->kartu_keluarga_id);
    }
}
