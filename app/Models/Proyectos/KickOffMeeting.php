<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KickOffMeeting extends Model
{
    use HasFactory;

    protected $table = 'kick_off_meetings';

    protected $fillable = [
        'proyecto_id',
        'tipo',
        'fecha',
        'participantes',
        'agenda',
        'minuta_pdf_path',
        'cronograma_attached_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'participantes' => 'array',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}
