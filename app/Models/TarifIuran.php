<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarifIuran extends Model
{
    protected $table = 'tarif_iuran';

    use HasUuids;

    protected $fillable = [
        'nama',
        'nominal',
        'periode_mulai',
        'periode_selesai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanIuran::class, 'tarif_id');
    }
}
