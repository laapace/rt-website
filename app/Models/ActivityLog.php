<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    use HasUuids;

    /**
     * Hanya created_at — tidak ada updated_at (lihat schema.md §3.16).
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'aksi',
        'tabel_terkait',
        'record_id',
        'detail',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'detail' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
