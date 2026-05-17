<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\ProyectoMiembro;
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
        'estado', 'tipo', 'ponderacion',
        'archivo_oferta',
        'concepto_adjudicacion', 'porcentaje_adjudicacion', 'cartera_esperada',
        // Campos de proyecto (post-adjudicación)
        'sublinea_id', 'usuario_final', 'sector',
        'plazo_estimado',
        'fecha_inicio_planeada', 'fecha_fin_planeada',
        'metodo_distribucion_plurianual',
        'notas',
    ];

    // ── Scopes de conveniencia ────────────────────────────────────────────
    public function scopeOportunidades($query)
    {
        return $query->where('tipo', 'oportunidad');
    }

    public function scopeProyectos($query)
    {
        return $query->where('tipo', 'proyecto');
    }

    public function esProyecto(): bool
    {
        return $this->tipo === 'proyecto';
    }

    public function esOportunidad(): bool
    {
        return $this->tipo === 'oportunidad';
    }

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

    public function directorDn(): HasOneThrough
    {
        return $this->hasOneThrough(\App\Models\User::class, ProyectoMiembro::class, 'proyecto_id', 'id', 'id', 'user_id')
                    ->where('proyecto_miembros.rol', 'director_dn');
    }

    public function gerenteProyectos(): HasOneThrough
    {
        return $this->hasOneThrough(\App\Models\User::class, ProyectoMiembro::class, 'proyecto_id', 'id', 'id', 'user_id')
                    ->where('proyecto_miembros.rol', 'gerente_proyectos');
    }

    public function gerenteOperaciones(): HasOneThrough
    {
        return $this->hasOneThrough(\App\Models\User::class, ProyectoMiembro::class, 'proyecto_id', 'id', 'id', 'user_id')
                    ->where('proyecto_miembros.rol', 'gerente_operaciones');
    }

    public function ingenieroCostos(): HasOneThrough
    {
        return $this->hasOneThrough(\App\Models\User::class, ProyectoMiembro::class, 'proyecto_id', 'id', 'id', 'user_id')
                    ->where('proyecto_miembros.rol', 'ingeniero_costos');
    }

    public function ingenieroProyectos(): HasOneThrough
    {
        return $this->hasOneThrough(\App\Models\User::class, ProyectoMiembro::class, 'proyecto_id', 'id', 'id', 'user_id')
                    ->where('proyecto_miembros.rol', 'ingeniero_proyectos');
    }

    public function trainee(): HasOneThrough
    {
        return $this->hasOneThrough(\App\Models\User::class, ProyectoMiembro::class, 'proyecto_id', 'id', 'id', 'user_id')
                    ->where('proyecto_miembros.rol', 'trainee');
    }

    // ── Accessors de ID (compatibilidad con código existente) ──────────────
    public function getDirectorDnIdAttribute(): ?int
    {
        return $this->relationLoaded('directorDn')
            ? $this->directorDn?->id
            : $this->getMiembroIdPorRol('director_dn');
    }

    public function getGerenteProyectosIdAttribute(): ?int
    {
        return $this->relationLoaded('gerenteProyectos')
            ? $this->gerenteProyectos?->id
            : $this->getMiembroIdPorRol('gerente_proyectos');
    }

    public function getGerenteOperacionesIdAttribute(): ?int
    {
        return $this->relationLoaded('gerenteOperaciones')
            ? $this->gerenteOperaciones?->id
            : $this->getMiembroIdPorRol('gerente_operaciones');
    }

    public function getIngenieroCostosIdAttribute(): ?int
    {
        return $this->relationLoaded('ingenieroCostos')
            ? $this->ingenieroCostos?->id
            : $this->getMiembroIdPorRol('ingeniero_costos');
    }

    public function getIngenieroProyectosIdAttribute(): ?int
    {
        return $this->relationLoaded('ingenieroProyectos')
            ? $this->ingenieroProyectos?->id
            : $this->getMiembroIdPorRol('ingeniero_proyectos');
    }

    public function getTraineeIdAttribute(): ?int
    {
        return $this->relationLoaded('trainee')
            ? $this->trainee?->id
            : $this->getMiembroIdPorRol('trainee');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(ProyectoEvento::class, 'proyecto_id');
    }

    public function historialPonderacion(): HasMany
    {
        return $this->hasMany(ProyectoPonderacionHistorial::class, 'proyecto_id');
    }

    /**
     * Registra (o actualiza si ya existe) un snapshot de ponderación para el
     * mes indicado. También sincroniza el cache `proyectos.ponderacion` cuando
     * el snapshot corresponde al mes vigente (el "valor actual").
     */
    public function registrarPonderacion(int $ponderacionId, ?int $anio = null, ?int $mes = null, ?string $notas = null): ProyectoPonderacionHistorial
    {
        $anio ??= (int) now()->year;
        $mes  ??= (int) now()->month;

        /** @var ProyectoPonderacionHistorial $snap */
        $snap = $this->historialPonderacion()->updateOrCreate(
            ['anio' => $anio, 'mes' => $mes],
            [
                'ponderacion_id' => $ponderacionId,
                'user_id'        => auth()->id(),
                'notas'          => $notas,
            ]
        );

        // Cache el porcentaje actual si el snapshot es del mes vigente
        if ($anio === (int) now()->year && $mes === (int) now()->month) {
            $porcentaje = (int) \App\Models\Ponderacion::whereKey($ponderacionId)->value('porcentaje');
            $this->forceFill(['ponderacion' => $porcentaje])->save();
        }

        return $snap;
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

    // ── Equipo normalizado ─────────────────────────────────────────────────
    public function miembros(): HasMany
    {
        return $this->hasMany(ProyectoMiembro::class, 'proyecto_id');
    }

    /**
     * Reemplaza el miembro con un rol específico (1:1 por rol).
     * Elimina el registro anterior con ese rol y crea uno nuevo.
     */
    public function setMiembroPorRol(?int $userId, string $rol): void
    {
        $this->miembros()->where('rol', $rol)->delete();

        if ($userId) {
            $this->miembros()->create(['user_id' => $userId, 'rol' => $rol]);
        }
    }

    /**
     * Devuelve el user_id del primer miembro que tenga el rol dado.
     */
    public function getMiembroIdPorRol(string $rol): ?int
    {
        return $this->miembros()->where('rol', $rol)->value('user_id');
    }
}
