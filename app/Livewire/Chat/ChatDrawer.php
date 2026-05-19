<?php

namespace App\Livewire\Chat;

use App\Events\ChatMessageRead;
use App\Events\ChatMessageSent;
use App\Events\ChatTyping;
use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMensaje;
use App\Notifications\Chat\NewChatMentionNotification;
use App\Notifications\Chat\NewChatMessageNotification;
use App\Notifications\Chat\NewChatReplyNotification;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Gate;
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
    public $replyingTo = null;
    public $expandedSections = ['proyecto', 'departamento', 'direccion', 'privado'];
    public $showNewChannel = false;
    public $showNewDm = false;
    public $dmSearch = '';
    public $dmUsers = [];

    protected $listeners = [
        'openChatDrawer' => 'openDrawer',
        'selectChannel' => 'selectChannel',
        'refreshChat' => '$refresh',
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

        $canales = ChatCanal::query()
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
            ->get();

        $canalIds = $canales->pluck('id');
        $lecturas = ChatLectura::where('user_id', $user->id)
            ->whereIn('canal_id', $canalIds)
            ->pluck('ultimo_mensaje_leido_id', 'canal_id');

        $ultimoMensajeIds = ChatMensaje::whereIn('canal_id', $canalIds)
            ->selectRaw('canal_id, MAX(id) as max_id')
            ->groupBy('canal_id')
            ->pluck('max_id', 'canal_id');

        $noLeidosByCanal = [];
        foreach ($canalIds as $canalId) {
            $ultimoLeidoId = $lecturas->get($canalId, 0);
            $ultimoId = $ultimoMensajeIds->get($canalId, 0);
            if ($ultimoId > $ultimoLeidoId) {
                $noLeidosByCanal[$canalId] = ChatMensaje::where('canal_id', $canalId)
                    ->where('id', '>', $ultimoLeidoId)
                    ->count();
            } else {
                $noLeidosByCanal[$canalId] = 0;
            }
        }

        $this->canales = $canales->map(function ($canal) use ($user, $noLeidosByCanal) {
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
                'no_leidos' => $noLeidosByCanal[$canal->id] ?? 0,
                'miembros' => $canal->miembros->map(fn ($m) => [
                    'id' => $m->user->id,
                    'name' => $m->user->name,
                    'avatar' => strtoupper(substr($m->user->name ?? 'U', 0, 2)),
                ]),
            ];
        })->toArray();
    }

    public function selectChannel($channelId)
    {
        $canal = ChatCanal::find($channelId);
        if (! $canal || ! Gate::allows('view', $canal)) {
            $this->activeChannelId = null;
            return;
        }

        $this->activeChannelId = (int) $channelId;
        $this->typingUsers = [];
        $this->replyingTo = null;
        $this->highlight = '';
        $this->editingMessageId = null;
        $this->editingContent = '';
        $this->loadMensajes();
        $this->marcarLeido();

        $this->dispatch('channel-selected', channelId: (int) $channelId);
        $this->dispatch('scroll-chat-to-bottom');
    }

    public function closeDrawer()
    {
        $this->open = false;
        $this->dispatch('channel-left');
    }

    public function loadMensajes()
    {
        if (! $this->activeChannelId) return;

        $messages = ChatMensaje::where('canal_id', $this->activeChannelId)
            ->when($this->highlight, function ($q) {
                $q->where('contenido', 'like', "%{$this->highlight}%");
            })
            ->with(['user', 'menciones.user', 'replies.user', 'replies.menciones.user'])
            ->latest()
            ->take(51)
            ->get();

        $this->hasMoreMessages = $messages->count() > 50;
        $messages = $messages->take(50);

        $this->mensajes = $messages->reverse()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'user_name' => $msg->user->name,
                'user_avatar' => strtoupper(substr($msg->user->name ?? 'U', 0, 2)),
                'contenido' => $msg->contenido,
                'created_at' => $msg->created_at->format('H:i'),
                'created_at_full' => $msg->created_at->diffForHumans(),
                'created_at_full_raw' => $msg->created_at->toIso8601String(),
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
        })->values()->toArray();

        $this->loadChannelMembers();
    }

    public function loadMoreMessages()
    {
        if (! $this->activeChannelId || empty($this->mensajes)) return;

        $firstMessageId = $this->mensajes[0]['id'];

        $olderMessages = ChatMensaje::where('canal_id', $this->activeChannelId)
            ->where('id', '<', $firstMessageId)
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
                    'created_at_full_raw' => $msg->created_at->toIso8601String(),
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
            })->values()->toArray();

        $this->mensajes = array_merge($olderMessages, $this->mensajes);
        $this->hasMoreMessages = count($olderMessages) >= 50;

        $this->dispatch('preserve-scroll-position');
    }

    public function loadChannelMembers()
    {
        if (! $this->activeChannelId) {
            $this->channelMembers = [];
            return;
        }

        $canal = ChatCanal::find($this->activeChannelId);
        if (! $canal) {
            $this->channelMembers = [];
            return;
        }

        $this->channelMembers = $canal->miembros()
            ->with('user')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->user->id,
                'name' => $m->user->name,
            ])
            ->toArray();
    }

    public function searchMembers($query)
    {
        if (strlen($query) < 1) {
            $this->loadChannelMembers();
            return;
        }

        $q = strtolower($query);
        $this->channelMembers = array_values(array_filter($this->channelMembers, fn ($m) => str_contains(strtolower($m['name']), $q)));
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

    public $editingMessageId = null;
    public $editingContent = '';
    public $hasMoreMessages = false;
    public $channelMembers = [];

    public function toggleChannelSection($tipo)
    {
        $index = array_search($tipo, $this->expandedSections);
        if ($index !== false) {
            unset($this->expandedSections[$index]);
            $this->expandedSections = array_values($this->expandedSections);
        } else {
            $this->expandedSections[] = $tipo;
        }
    }

    public function sendMessage()
    {
        if ((empty(trim($this->newMessage)) && empty($this->attachments)) || ! $this->activeChannelId) return;

        $canal = ChatCanal::find($this->activeChannelId);
        if (! $canal || ! Gate::allows('view', $canal)) return;

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
            'parent_message_id' => $this->replyingTo,
            'contenido' => trim($this->newMessage) ?: '',
            'attachments' => ! empty($attachmentData) ? $attachmentData : null,
        ]);

        preg_match_all('/@(\w+)/', $this->newMessage, $matches);
        if (! empty($matches[1])) {
            $mentionedUsers = \App\Models\User::whereIn('name', $matches[1])->get();
            foreach ($mentionedUsers as $user) {
                $mensaje->menciones()->create(['user_id' => $user->id]);

                if ($user->id !== auth()->id()) {
                    $user->notify(new NewChatMentionNotification(
                        $this->activeChannelId,
                        $canal->nombre,
                        auth()->user()->name,
                        $mensaje->id,
                    ));
                }
            }
        }

        if ($this->replyingTo) {
            $parentMensaje = ChatMensaje::find($this->replyingTo);
            if ($parentMensaje && $parentMensaje->user_id !== auth()->id()) {
                $parentMensaje->user->notify(new NewChatReplyNotification(
                    $this->activeChannelId,
                    $canal->nombre,
                    auth()->user()->name,
                    $mensaje->id,
                ));
            }
        }

        $canal->miembros()
            ->where('user_id', '!=', auth()->id())
            ->each(function ($miembro) use ($canal, $mensaje) {
                $miembro->user->notify(new NewChatMessageNotification(
                    $canal->id,
                    $canal->nombre,
                    auth()->user()->name,
                    $mensaje->id,
                ));
            });

        try {
            ChatMessageSent::dispatch($mensaje->load('user'));
        } catch (\Exception $e) {
        }

        $this->newMessage = '';
        $this->attachments = [];
        $this->replyingTo = null;
        $this->loadMensajes();
        $this->loadCanales();
        $this->marcarLeido();
        $this->dispatch('scroll-chat-to-bottom');
    }

    public function typing()
    {
        if (! $this->activeChannelId) return;

        try {
            ChatTyping::dispatch(
                $this->activeChannelId,
                auth()->id(),
                auth()->user()->name,
                true
            );
        } catch (\Exception $e) {
        }
    }

    public function stopTyping()
    {
        if (! $this->activeChannelId) return;

        try {
            ChatTyping::dispatch(
                $this->activeChannelId,
                auth()->id(),
                auth()->user()->name,
                false
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

        \App\Models\Chat\ChatMencion::where('user_id', auth()->id())
            ->whereHas('mensaje', function ($q) {
                $q->where('canal_id', $this->activeChannelId);
            })
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);

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

        if ((int) $data['canal_id'] === (int) $this->activeChannelId) {
            $this->loadMensajes();
        }

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

    public function editMessage($messageId)
    {
        $mensaje = ChatMensaje::find($messageId);
        if (! $mensaje) return;

        if ($mensaje->user_id !== auth()->id() && ! auth()->user()->esAdmin()) {
            return;
        }

        $this->editingMessageId = $messageId;
        $this->editingContent = $mensaje->contenido;
    }

    public function updateMessage()
    {
        if (! $this->editingMessageId) return;

        $mensaje = ChatMensaje::find($this->editingMessageId);
        if (! $mensaje) return;

        if ($mensaje->user_id !== auth()->id() && ! auth()->user()->esAdmin()) {
            return;
        }

        $trimmed = trim($this->editingContent);
        if (empty($trimmed)) return;

        $mensaje->update([
            'contenido' => $trimmed,
            'edited_at' => now(),
        ]);

        $this->editingMessageId = null;
        $this->editingContent = '';
        $this->loadMensajes();
    }

    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->editingContent = '';
    }

    public function deleteMessage($messageId)
    {
        $mensaje = ChatMensaje::find($messageId);
        if (! $mensaje) return;

        if ($mensaje->user_id !== auth()->id() && ! auth()->user()->esAdmin()) {
            return;
        }

        $mensaje->update(['contenido' => 'Este mensaje fue eliminado', 'edited_at' => null]);

        $this->loadMensajes();
        $this->loadCanales();
    }

    public function searchDmUsers()
    {
        if (strlen($this->dmSearch) < 2) {
            $this->dmUsers = [];
            return;
        }

        $this->dmUsers = \App\Models\User::where('status', 'active')
            ->where('id', '!=', auth()->id())
            ->where('name', 'like', "%{$this->dmSearch}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->toArray();
    }

    public function startDm($userId)
    {
        $chatService = new ChatService();
        $canal = $chatService->createPrivateChannel(auth()->id(), (int) $userId);

        $this->showNewDm = false;
        $this->dmSearch = '';
        $this->dmUsers = [];
        $this->loadCanales();
        $this->selectChannel($canal->id);
        $this->open = true;
    }

    public function render()
    {
        if ($this->mode === 'page') {
            return view('livewire.chat.chat-panel')
                ->layout('components.layouts.app', ['fullWidth' => true]);
        }

        return view('livewire.chat.chat-drawer');
    }
}
