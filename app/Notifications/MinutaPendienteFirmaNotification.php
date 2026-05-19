<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MinutaPendienteFirmaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $dnNumero,
        private string $creadaPor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
