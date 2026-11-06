<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PengaturanRt extends Model
{
    protected $table = 'pengaturan_rt';

    use HasUuids;

    protected $fillable = [
        'nama_rt',
        'nama_rw',
        'kelurahan',
        'kecamatan',
        'kota',
        'alamat',
        'telepon',
        'nama_ketua',
    ];
}
