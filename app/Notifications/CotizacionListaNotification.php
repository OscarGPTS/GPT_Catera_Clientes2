<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CotizacionListaNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $cpNumero,
        private string $generadaPor,
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
            'type' => 'cotizacion_lista',
            'icon' => 'banknotes',
            'title' => "Cotización {$this->cpNumero} lista",
            'message' => "{$this->generadaPor} ha completado la cotización de {$this->cpNumero}.",
            'link' => "/proyectos/{$this->proyectoId}/cotizacion",
            'action' => 'Ver cotización',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
