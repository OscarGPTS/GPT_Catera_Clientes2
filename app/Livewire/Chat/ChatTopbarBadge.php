<?php

namespace App\Livewire\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMensaje;
use Livewire\Component;

class ChatTopbarBadge extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadUnread();
    }

    public function loadUnread(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->unreadCount = 0;
            return;
        }

        $canalIds = ChatCanal::whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        if ($canalIds->isEmpty()) {
            $this->unreadCount = 0;
            return;
        }

        $lecturas = ChatLectura::where('user_id', $user->id)
            ->whereIn('canal_id', $canalIds)
            ->pluck('ultimo_mensaje_leido_id', 'canal_id');

        $total = 0;
        foreach ($canalIds as $canalId) {
            $ultimoLeidoId = $lecturas->get($canalId, 0);
            $total += ChatMensaje::where('canal_id', $canalId)
                ->where('id', '>', $ultimoLeidoId)
                ->where('user_id', '!=', $user->id)
                ->count();
        }

        $this->unreadCount = $total;
    }

    public function render()
    {
        return view('livewire.chat.chat-topbar-badge');
    }
}
