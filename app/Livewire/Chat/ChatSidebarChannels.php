<?php

namespace App\Livewire\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMensaje;
use Livewire\Component;

class ChatSidebarChannels extends Component
{
    public $canales = [];
    public $totalNoLeidos = 0;

    protected $listeners = [
        'refreshChat' => '$refresh',
    ];

    public function mount()
    {
        $this->loadCanales();
    }

    public function loadCanales()
    {
        $user = auth()->user();

        $canales = ChatCanal::query()
            ->whereHas('miembros', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['ultimoMensaje.user'])
            ->orderByDesc(
                ChatMensaje::select('created_at')
                    ->whereColumn('canal_id', 'chat_canales.id')
                    ->latest()
                    ->take(1)
            )
            ->take(8)
            ->get()
            ->map(function ($canal) use ($user) {
                $lectura = $canal->lecturas()->where('user_id', $user->id)->first();
                $ultimoLeidoId = $lectura ? $lectura->ultimo_mensaje_leido_id : 0;
                $ultimoMensajeId = $canal->ultimoMensaje ? $canal->ultimoMensaje->id : 0;
                $noLeidos = $ultimoMensajeId > $ultimoLeidoId
                    ? $canal->mensajes()->where('id', '>', $ultimoLeidoId)->count()
                    : 0;

                return [
                    'id' => $canal->id,
                    'nombre' => $canal->nombre,
                    'tipo' => $canal->tipo,
                    'ultimo_mensaje' => $canal->ultimoMensaje ? \Illuminate\Support\Str::limit($canal->ultimoMensaje->contenido, 40) : null,
                    'no_leidos' => $noLeidos,
                ];
            })
            ->toArray();

        $this->canales = $canales;
        $this->totalNoLeidos = collect($canales)->sum('no_leidos');
    }

    public function openChannel($channelId)
    {
        $this->dispatch('open-chat-with-channel', channelId: (int) $channelId);
    }

    public function render()
    {
        return view('livewire.chat.chat-sidebar-channels');
    }
}
