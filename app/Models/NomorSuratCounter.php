<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSuratCounter extends Model
{
    protected $table = 'nomor_surat_counter';

    /**
     * PK = tahun (int), bukan id auto-increment.
     * Hanya kolom updated_at — tidak ada created_at.
     */
    protected $primaryKey = 'tahun';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'tahun',
        'nomor_terakhir',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }
}
