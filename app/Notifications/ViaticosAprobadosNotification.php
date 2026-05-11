<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ViaticosAprobadosNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $proyectoNombre,
        private string $periodo,
        private string $aprobadoPor,
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
        return [
            'type' => 'viaticos_aprobados',
            'icon' => 'currency-dollar',
            'title' => 'Viáticos aprobados',
            'message' => "{$this->aprobadoPor} ha aprobado los viáticos de {$this->proyectoNombre} para {$this->periodo}.",
            'link' => "/proyectos/{$this->proyectoId}",
            'action' => 'Ver viáticos',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
