<?php

namespace App\Events;

use App\Models\Chat\ChatMensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMensaje $mensaje,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.canal.' . $this->mensaje->canal_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $user = $this->mensaje->user;

        return [
            'id' => $this->mensaje->id,
            'canal_id' => $this->mensaje->canal_id,
            'user_id' => $this->mensaje->user_id,
            'user_name' => $user->name,
            'user_avatar' => strtoupper(substr($user->name ?? 'U', 0, 2)),
            'contenido' => $this->mensaje->contenido,
            'parent_message_id' => $this->mensaje->parent_message_id,
            'created_at' => $this->mensaje->created_at->format('H:i'),
            'created_at_full' => $this->mensaje->created_at->diffForHumans(),
            'attachments' => $this->mensaje->attachments,
            'edited' => false,
        ];
    }
}
