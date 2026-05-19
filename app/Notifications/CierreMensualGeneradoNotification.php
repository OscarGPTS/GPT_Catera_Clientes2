<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CierreMensualGeneradoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $mes,
        private int $año,
        private string $generadoPor,
        private int $cierreId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mesLabel = $meses[$this->mes - 1] ?? "Mes {$this->mes}";

        return [
            'type' => 'cierre_mensual',
            'icon' => 'calculator',
            'title' => "Cierre {$mesLabel} {$this->año} generado",
            'message' => "{$this->generadoPor} ha generado el cierre mensual de {$mesLabel} {$this->año}.",
            'link' => "/finanzas/cierres/{$this->cierreId}",
            'action' => 'Ver cierre',
        ];
    }
}
