<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DossierIncompletoAlertaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $proyectoId,
        private string $dnNumero,
        private int $porcentaje,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'dossier_incompleto',
            'icon' => 'exclamation-triangle',
            'title' => "Dossier {$this->dnNumero} incompleto",
            'message' => "El dossier de {$this->dnNumero} está al {$this->porcentaje}%. Se requiere 100% para el cierre.",
            'link' => "/proyectos/{$this->proyectoId}/libro",
            'action' => 'Completar dossier',
            'proyecto_id' => $this->proyectoId,
        ];
    }
}
