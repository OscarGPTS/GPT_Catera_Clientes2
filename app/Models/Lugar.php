<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lugar extends Model
{
    protected $table = 'lugares';

    protected $fillable = [
        'nombre',
        'tipo',   // estado, ciudad, municipio, pais
        'pais',
        'codigo',
    ];

    public function proyectos(): HasMany
    {
        return $this->hasMany(\App\Models\Proyectos\Proyecto::class, 'lugar_id');
    }

    /**
     * Obtener o crear un lugar por nombre (para uso en seeders y formularios).
     */
    public static function firstOrCreate2(string $nombre, string $tipo = 'estado', string $pais = 'México'): static
    {
        return static::firstOrCreate(
            ['nombre' => $nombre],
            ['tipo' => $tipo, 'pais' => $pais]
        );
    }
}
