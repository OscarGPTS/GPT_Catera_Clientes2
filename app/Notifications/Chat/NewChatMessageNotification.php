<?php

namespace App\Notifications\Chat;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $canalId,
        private string $canalNombre,
        private string $userName,
        private int $mensajeId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }


    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'chat_message',
            'icon' => 'chat-bubble-left-right',
            'title' => "Mensaje en {$this->canalNombre}",
            'message' => "{$this->userName} envió un mensaje en {$this->canalNombre}.",
            'link' => "/chat?canal={$this->canalId}",
            'action' => 'Ver mensaje',
            'canal_id' => $this->canalId,
            'mensaje_id' => $this->mensajeId,
        ];
    }
}