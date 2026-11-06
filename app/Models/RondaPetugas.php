<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RondaPetugas extends Model
{
    use HasUuids;

    protected $table = 'ronda_petugas';

    protected $fillable = [
        'jadwal_ronda_id',
        'warga_id',
    ];

    public function jadwalRonda(): BelongsTo
    {
        return $this->belongsTo(JadwalRonda::class);
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }
}
