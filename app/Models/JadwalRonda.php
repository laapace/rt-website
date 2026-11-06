<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JadwalRonda extends Model
{
    protected $table = 'jadwal_ronda';

    use HasUuids;

    protected $fillable = [
        'tanggal',
        'blok',
        'keterangan',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function petugas(): BelongsToMany
    {
        // using() wajib: pivot RondaPetugas memakai HasUuids, tanpa ini
        // sync()/attach() menyimpan id pivot NULL dan gagal di database.
        return $this->belongsToMany(Warga::class, 'ronda_petugas')
            ->using(RondaPetugas::class)
            ->withTimestamps();
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
