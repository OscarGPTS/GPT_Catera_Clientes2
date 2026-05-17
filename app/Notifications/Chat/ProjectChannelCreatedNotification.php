<?php

namespace App\Notifications\Chat;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectChannelCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $canalId,
        private string $canalNombre,
        private string $cpNumero,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_channel_created',
            'icon' => 'clipboard-document-list',
            'title' => "Canal del proyecto {$this->cpNumero}",
            'message' => "Se creó el canal {$this->canalNombre} para el proyecto {$this->cpNumero}.",
            'link' => "/chat?canal={$this->canalId}",
            'action' => 'Abrir canal',
            'canal_id' => $this->canalId,
        ];
    }
}