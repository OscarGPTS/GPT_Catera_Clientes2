<?php

namespace App\Notifications\Chat;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChannelMemberAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private int $canalId,
        private string $canalNombre,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'channel_member_added',
            'icon' => 'user-plus',
            'title' => "Agregado a {$this->canalNombre}",
            'message' => "Fuiste agregado al canal {$this->canalNombre}.",
            'link' => "/chat?canal={$this->canalId}",
            'action' => 'Abrir canal',
            'canal_id' => $this->canalId,
        ];
    }
}