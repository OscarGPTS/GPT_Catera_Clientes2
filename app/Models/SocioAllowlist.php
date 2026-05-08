<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocioAllowlist extends Model
{
    protected $table = 'socios_allowlist';

    protected $fillable = [
        'email',
        'notes',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }
}