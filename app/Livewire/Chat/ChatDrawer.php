<?php

namespace App\Livewire\Chat;

use App\Events\ChatMessageRead;
use App\Events\ChatMessageSent;
use App\Events\ChatTyping;
use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMensaje;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatDrawer extends Component
{
    use WithFileUploads;

    public $open = false;
    public $activeChannelId = null;
    public $canales = [];
    public $mensajes = [];
    public $newMessage = '';
    public $mode = 'drawer';
    public $search = '';
    public $typingUsers = [];
    public $attachments = [];
    public $uploading = false;
    public $highlight = '';

    protected $listeners = [
        'openChatDrawer' => 'openDrawer',
        'selectChannel' => 'selectChannel',
        'refreshChat' => '$refresh',
        'echoMessageSent' => 'handleIncomingMessage',
        'echoMessageRead' => 'handleReadReceipt',
        'echoTyping' => 'handleTyping',
        'echoStopTyping' => 'handleStopTyping',
        'open-chat-with-channel' => 'openDrawerFromSidebar',
    ];

    public function openDrawerFromSidebar($channelId)
    {
        $this->selectChannel($channelId);
        $this->open = true;
    }

    public function mount()
    {
        $this->loadCanales();

        if (request()->route() && request()->route()->getName() === 'chat.index') {
            $this->mode = 'page';
        }
    }

    public function openDrawer($channelId = null)
    {
        $this->open = true;
        if ($channelId) {
            $this->selectChannel($channelId);
        }
    }

    public function loadCanales()
    {
        $user = auth()->user();

        $this->canales = ChatCanal::query()
            ->whereHas('miembros', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['ultimoMensaje.user', 'miembros.user'])
            ->when($this->search, function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%");
            })
            ->orderByDesc(
                ChatMensaje::select('created_at')
                    ->whereColumn('canal_id', 'chat_canales.id')
                    ->latest()
                    ->take(1)
            )
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
                    'descripcion' => $canal->descripcion,
                    'ultimo_mensaje' => $canal->ultimoMensaje ? [
                        'contenido' => \Illuminate\Support\Str::limit($canal->ultimoMensaje->contenido, 50),
                        'user_name' => $canal->ultimoMensaje->user->name ?? '',
                        'created_at' => $canal->ultimoMensaje->created_at->diffForHumans(),
                    ] : null,
                    'no_leidos' => $noLeidos,
                    'miembros' => $canal->miembros->map(fn ($m) => [
                        'id' => $m->user->id,
                        'name' => $m->user->name,
                        'avatar' => strtoupper(substr($m->user->name ?? 'U', 0, 2)),
                    ]),
                ];
            })
            ->toArray();
    }

    public function selectChannel($channelId)
    {
        $this->activeChannelId = (int) $channelId;
        $this->typingUsers = [];
        $this->loadMensajes();
        $this->marcarLeido();

        $this->dispatch('channel-selected', channelId: (int) $channelId);
    }

    public function closeDrawer()
    {
        $this->open = false;
        $this->dispatch('channel-left');
    }

    public function loadMensajes()
    {
        if (! $this->activeChannelId) return;

        $this->mensajes = ChatMensaje::where('canal_id', $this->activeChannelId)
            ->when($this->highlight, function ($q) {
                $q->where('contenido', 'like', "%{$this->highlight}%");
            })
            ->with(['user', 'menciones.user', 'replies.user', 'replies.menciones.user'])
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'user_name' => $msg->user->name,
                    'user_avatar' => strtoupper(substr($msg->user->name ?? 'U', 0, 2)),
                    'contenido' => $msg->contenido,
                    'created_at' => $msg->created_at->format('H:i'),
                    'created_at_full' => $msg->created_at->diffForHumans(),
                    'parent_id' => $msg->parent_message_id,
                    'replies' => $msg->replies->map(fn ($r) => [
                        'id' => $r->id,
                        'user_name' => $r->user->name,
                        'user_avatar' => strtoupper(substr($r->user->name ?? 'U', 0, 2)),
                        'contenido' => $r->contenido,
                        'created_at' => $r->created_at->format('H:i'),
                    ])->toArray(),
                    'attachments' => $msg->attachments,
                    'edited' => ! is_null($msg->edited_at),
                    'is_mine' => $msg->user_id === auth()->id(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function updatedAttachments()
    {
        $this->validate([
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar',
        ]);
    }

    public function uploadAttachment()
    {
        // Triggered by Alpine file input change — handled by Livewire binding
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage)) && empty($this->attachments) || ! $this->activeChannelId) return;

        $attachmentData = [];
        foreach ($this->attachments as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('chat-attachments', $filename, 'public');
            $attachmentData[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => Storage::disk('public')->url($path),
            ];
        }

        $mensaje = ChatMensaje::create([
            'canal_id' => $this->activeChannelId,
            'user_id' => auth()->id(),
            'contenido' => trim($this->newMessage) ?: '',
            'attachments' => ! empty($attachmentData) ? $attachmentData : null,
        ]);

        preg_match_all('/@(\w+)/', $this->newMessage, $matches);
        if (! empty($matches[1])) {
            $mentionedUsers = \App\Models\User::whereIn('name', $matches[1])->get();
            foreach ($mentionedUsers as $user) {
                $mensaje->menciones()->create(['user_id' => $user->id]);
            }
        }

        try {
            ChatMessageSent::dispatch($mensaje->load('user'));
        } catch (\Exception $e) {
            // Broadcast failed silently — message is still persisted
        }

        $this->newMessage = '';
        $this->attachments = [];
        $this->loadMensajes();
        $this->loadCanales();
        $this->marcarLeido();
    }

    public function replyTo($messageId)
    {
        $parent = ChatMensaje::find($messageId);
        if ($parent && ! empty(trim($this->newMessage))) {
            $mensaje = ChatMensaje::create([
                'canal_id' => $this->activeChannelId,
                'user_id' => auth()->id(),
                'parent_message_id' => $messageId,
                'contenido' => trim($this->newMessage),
            ]);

            try {
                ChatMessageSent::dispatch($mensaje->load('user'));
            } catch (\Exception $e) {
            }

            $this->newMessage = '';
            $this->loadMensajes();
        }
    }

    public function typing()
    {
        if (! $this->activeChannelId) return;

        try {
            ChatTyping::dispatch(
                $this->activeChannelId,
                auth()->id(),
                auth()->user()->name,
                ! empty($this->newMessage)
            );
        } catch (\Exception $e) {
        }
    }

    public function marcarLeido()
    {
        if (! $this->activeChannelId) return;

        $ultimo = ChatMensaje::where('canal_id', $this->activeChannelId)->latest()->first();
        if (! $ultimo) return;

        ChatLectura::updateOrCreate(
            ['canal_id' => $this->activeChannelId, 'user_id' => auth()->id()],
            ['ultimo_mensaje_leido_id' => $ultimo->id]
        );

        try {
            ChatMessageRead::dispatch(
                $this->activeChannelId,
                auth()->id(),
                auth()->user()->name,
                $ultimo->id
            );
        } catch (\Exception $e) {
        }

        $this->loadCanales();
    }

    public function handleIncomingMessage($data)
    {
        if (! isset($data['canal_id']) || ! $this->activeChannelId) return;
        if ((int) $data['canal_id'] !== (int) $this->activeChannelId) return;

        $this->loadMensajes();
        $this->loadCanales();
    }

    public function handleReadReceipt($data)
    {
        if (! isset($data['canalId']) || (int) $data['canalId'] !== (int) $this->activeChannelId) return;
        $this->loadCanales();
    }

    public function handleTyping($data)
    {
        if (! isset($data['canalId']) || (int) $data['canalId'] !== (int) $this->activeChannelId) return;
        if ((int) $data['userId'] === auth()->id()) return;

        $this->typingUsers[$data['userId']] = $data['userName'] ?? 'Alguien';
    }

    public function handleStopTyping($data)
    {
        if (isset($data['userId'])) {
            unset($this->typingUsers[$data['userId']]);
        }
    }

    public function render()
    {
        if ($this->mode === 'page') {
            return view('livewire.chat.chat-panel')
                ->layout('components.layouts.app');
        }

        return view('livewire.chat.chat-drawer');
    }
}
