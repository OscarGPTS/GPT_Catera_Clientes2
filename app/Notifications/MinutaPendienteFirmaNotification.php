<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class MinutaPendienteFirmaNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $dnNumero,
        private string $creadaPor,
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
            'type' => 'minuta_pendiente_firma',
            'icon' => 'document-text',
            'title' => "Minuta pendiente de firma",
            'message' => "{$this->creadaPor} ha creado la minuta de entrega del proyecto {$this->dnNumero}. Pendiente de tu firma.",
            'link' => "/proyectos/{$this->proyectoId}/minuta",
            'action' => 'Firmar minuta',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
