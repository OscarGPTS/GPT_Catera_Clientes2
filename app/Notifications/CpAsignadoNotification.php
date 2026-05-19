<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CpAsignadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $cpNumero,
        private string $asignadoPor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cp_asignado',
            'icon' => 'clipboard-document-list',
            'title' => "CP {$this->cpNumero} asignado",
            'message' => "{$this->asignadoPor} te ha asignado el CP {$this->cpNumero}.",
            'link' => "/proyectos/{$this->proyectoId}",
            'action' => 'Ver proyecto',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
