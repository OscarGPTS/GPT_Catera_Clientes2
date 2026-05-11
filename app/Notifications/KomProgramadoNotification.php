<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class KomProgramadoNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $proyectoNombre,
        private string $fecha,
        private string $tipo, // interno | cliente
        private string $organizadoPor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray(object $notifiable): array
    {
        $tipoLabel = $this->tipo === 'interno' ? 'KOM Interno' : 'KOM Cliente';

        return [
            'type' => 'kom_programado',
            'icon' => 'presentation-chart-line',
            'title' => "{$tipoLabel} programado",
            'message' => "{$this->organizadoPor} ha programado un {$tipoLabel} para {$this->proyectoNombre} el {$this->fecha}.",
            'link' => "/proyectos/{$this->proyectoId}",
            'action' => 'Ver KOM',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
