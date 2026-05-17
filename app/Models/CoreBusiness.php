<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreBusiness extends Model
{
    protected $table = 'core_businesses';

    protected $fillable = [
        'core_business',
        'acronym',
        'description',
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
