<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\SolicitudInterna;

class Proyecto extends Model
{
    use SoftDeletes;

    protected $table = 'proyectos';

    protected $fillable = [
        // Campos del CSV / oportunidad
        'cp_numero', 'dn_numero', 'tech_reference', 'anio',
        'cliente_id', 'contacto', 'datos_contacto',
        'lugar_id', 'alcance',
        'fecha_envio', 'fecha_modificacion_oferta',
        'monto_usd', 'hitos_pago',
        'elaboro_id',
        'estado', 'ponderacion',
        'archivo_oferta',
        'concepto_adjudicacion', 'porcentaje_adjudicacion', 'cartera_esperada',
        // Campos de proyecto (post-adjudicación)
        'sublinea_id', 'usuario_final', 'sector',
        'fecha_inicio_planeada', 'fecha_fin_planeada',
        'metodo_distribucion_plurianual',
        'director_dn_id', 'gerente_proyectos_id', 'gerente_operaciones_id',
        'ingeniero_costos_id', 'ingeniero_proyectos_id', 'trainee_id',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'ponderacion' => 'integer',
            'monto_usd' => 'decimal:2',
            'porcentaje_adjudicacion' => 'decimal:2',
            'cartera_esperada' => 'decimal:2',
            'fecha_inicio_planeada' => 'date',
            'fecha_fin_planeada' => 'date',
            'fecha_envio' => 'date',
            'fecha_modificacion_oferta' => 'date',
        ];
    }

    public function elaboro(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'elaboro_id');
    }

    public function lugar(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Lugar::class, 'lugar_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Comercial\Cliente::class, 'cliente_id');
    }

    public function sublinea(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Comercial\Sublinea::class, 'sublinea_id');
    }

    public function directorDn(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'director_dn_id');
    }

    public function gerenteProyectos(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'gerente_proyectos_id');
    }

    public function gerenteOperaciones(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'gerente_operaciones_id');
    }

    public function ingenieroCostos(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'ingeniero_costos_id');
    }

    public function ingenieroProyectos(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'ingeniero_proyectos_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'trainee_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(ProyectoEvento::class, 'proyecto_id');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'proyecto_id');
    }

    public function solicitudesInterna(): HasMany
    {
        return $this->hasMany(SolicitudInterna::class, 'proyecto_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionPersona::class, 'proyecto_id');
    }

    public function bomBoeItems(): HasMany
    {
        return $this->hasMany(BomBoeItem::class, 'proyecto_id');
    }

    public function bitacoras(): HasMany
    {
        return $this->hasMany(BitacoraDiaria::class, 'proyecto_id');
    }

    public function libroProyecto()
    {
        return $this->hasOne(LibroProyecto::class, 'proyecto_id');
    }
}
