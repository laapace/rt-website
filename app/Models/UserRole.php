<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    protected $table = 'user_roles';

    use HasUuids;

    public function getNamaAttribute(): ?string
    {
        return $this->attributes['peran'] ?? null;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['peran'] ?? null;
    }

    protected $fillable = [
        'user_id',
        'peran',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
