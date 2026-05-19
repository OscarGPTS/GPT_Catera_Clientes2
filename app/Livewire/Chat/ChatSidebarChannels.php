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
        'refreshChat' => 'loadCanales',
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
            ->with(['ultimoMensaje.user', 'miembros.user'])
            ->orderByDesc(
                ChatMensaje::select('created_at')
                    ->whereColumn('canal_id', 'chat_canales.id')
                    ->latest()
                    ->take(1)
            )
            ->get();

        $canalIds = $canales->pluck('id');
        $lecturas = ChatLectura::where('user_id', $user->id)
            ->whereIn('canal_id', $canalIds)
            ->pluck('ultimo_mensaje_leido_id', 'canal_id');

        $ultimoMensajeIds = ChatMensaje::whereIn('canal_id', $canalIds)
            ->selectRaw('canal_id, MAX(id) as max_id')
            ->groupBy('canal_id')
            ->pluck('max_id', 'canal_id');

        $this->canales = $canales->map(function ($canal) use ($user, $lecturas, $ultimoMensajeIds) {
            $ultimoLeidoId = $lecturas->get($canal->id, 0);
            $ultimoId = $ultimoMensajeIds->get($canal->id, 0);
            $noLeidos = $ultimoId > $ultimoLeidoId
                ? ChatMensaje::where('canal_id', $canal->id)->where('id', '>', $ultimoLeidoId)->count()
                : 0;

            $displayName = $canal->nombre;
            if ($canal->tipo === 'privado') {
                $otherMember = $canal->miembros->first(fn ($m) => $m->user_id !== $user->id);
                $displayName = $otherMember ? $otherMember->user->name : $canal->nombre;
            }

            return [
                'id' => $canal->id,
                'nombre' => $displayName,
                'tipo' => $canal->tipo,
                'ultimo_mensaje' => $canal->ultimoMensaje ? \Illuminate\Support\Str::limit($canal->ultimoMensaje->contenido, 40) : null,
                'no_leidos' => $noLeidos,
            ];
        })->toArray();

        $this->totalNoLeidos = collect($this->canales)->sum('no_leidos');
    }

    public function handleNewMessage($data)
    {
        $this->loadCanales();
    }

    public static function getUnreadCount(): int
    {
        $user = auth()->user();
        if (! $user) return 0;

        $canalIds = ChatCanal::whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))->pluck('id');
        if ($canalIds->isEmpty()) return 0;

        $lecturas = ChatLectura::where('user_id', $user->id)
            ->whereIn('canal_id', $canalIds)
            ->pluck('ultimo_mensaje_leido_id', 'canal_id');

        $count = 0;
        foreach ($canalIds as $cid) {
            $lastId = ChatMensaje::where('canal_id', $cid)->max('id') ?? 0;
            if ($lastId > ($lecturas->get($cid) ?? 0)) $count++;
        }
        return $count;
    }

    public function render()
    {
        return view('livewire.chat.chat-sidebar-channels');
    }
}