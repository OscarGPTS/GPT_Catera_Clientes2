<?php

namespace App\Notifications\Chat;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewChatReplyNotification extends Notification implements ShouldBroadcast
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
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'chat_reply',
            'icon' => 'chat-bubble-left-right',
            'title' => "Respuesta en {$this->canalNombre}",
            'message' => "{$this->userName} respondió a tu mensaje en {$this->canalNombre}.",
            'link' => "/chat?canal={$this->canalId}",
            'action' => 'Ver respuesta',
            'canal_id' => $this->canalId,
            'mensaje_id' => $this->mensajeId,
        ];
    }
}