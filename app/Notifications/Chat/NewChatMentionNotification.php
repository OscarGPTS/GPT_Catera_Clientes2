<?php

namespace App\Notifications\Chat;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewChatMentionNotification extends Notification implements ShouldBroadcast
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
            'type' => 'chat_mention',
            'icon' => 'at-symbol',
            'title' => "Te mencionaron en {$this->canalNombre}",
            'message' => "{$this->userName} te mencionó en {$this->canalNombre}.",
            'link' => "/chat?canal={$this->canalId}",
            'action' => 'Ver mención',
            'canal_id' => $this->canalId,
            'mensaje_id' => $this->mensajeId,
        ];
    }
}