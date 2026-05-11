<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $canalId,
        public int $userId,
        public string $userName,
        public bool $typing,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.canal.' . $this->canalId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing';
    }
}
