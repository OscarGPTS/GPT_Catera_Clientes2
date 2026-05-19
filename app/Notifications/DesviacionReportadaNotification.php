<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DesviacionReportadaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $proyectoNombre,
        private string $fecha,
        private string $reportadaPor,
        private string $detalle,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'desviacion_reportada',
            'icon' => 'exclamation-circle',
            'title' => "Desviación en {$this->proyectoNombre}",
            'message' => "{$this->reportadaPor} reportó una desviación el {$this->fecha}: {$this->detalle}",
            'link' => "/proyectos/{$this->proyectoId}",
            'action' => 'Ver proyecto',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
