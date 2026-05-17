<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $table = 'sizes';

    protected $fillable = [
        'size_principal',
        'size_secundario',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size_principal' => 'decimal:2',
            'status'         => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
