<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'razon_social', 'alias', 'rfc', 'sector', 'segmento', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(ContactoCliente::class, 'cliente_id');
    }

    public function proyectos(): HasMany
    {
        return $this->hasMany(\App\Models\Proyectos\Proyecto::class, 'cliente_id');
    }
}
