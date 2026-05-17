<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vario extends Model
{
    protected $table = 'varios';

    protected $fillable = [
        'nombre',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
