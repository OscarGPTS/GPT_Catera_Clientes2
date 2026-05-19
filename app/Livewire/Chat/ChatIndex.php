<?php

namespace App\Livewire\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMensaje;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatIndex extends Component
{
    use WithFileUploads;

    public $activeChannelId = null;
    public $canales = [];
    public $mensajes = [];
    public $newMessage = '';
    public $search = '';
    public $userSearch = '';
    public $typingUsers = [];
    public $attachments = [];
    public $highlight = '';
    public $replyingTo = null;
    public $hasMoreMessages = false;
    public $editingMessageId = null;
    public $editingContent = '';
    public $channelMembers = [];
    public $showNewDm = false;
    public $dmSearch = '';
    public $dmUsers = [];
    public $expandedSections = ['proyecto', 'departamento', 'direccion', 'privado', 'grupo'];
    public $tab = 'conversations';
    public $showCreateGroup = false;
    public $groupName = '';
    public $groupDescription = '';
    public $groupMemberIds = [];
    public $groupSearch = '';
    public $groupSearchResults = [];

    protected $listeners = [];

    public function mount()
    {
        $this->loadCanales();
        $canalId = request()->query('canal');
        if ($canalId) {
            $this->selectChannel((int) $canalId);
        }
    }

    public function loadCanales()
    {
        $user = auth()->user();

        $canales = ChatCanal::query()
            ->whereHas('miembros', fn ($q) => $q->where('user_id', $user->id))
            ->with(['ultimoMensaje.user', 'miembros.user'])
            ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
            ->orderByDesc(ChatMensaje::select('created_at')->whereColumn('canal_id', 'chat_canales.id')->latest()->take(1))
            ->get();

        $canalIds = $canales->pluck('id');
        $lecturas = ChatLectura::where('user_id', $user->id)->whereIn('canal_id', $canalIds)->pluck('ultimo_mensaje_leido_id', 'canal_id');
        $ultimoMensajeIds = ChatMensaje::whereIn('canal_id', $canalIds)->selectRaw('canal_id, MAX(id) as max_id')->groupBy('canal_id')->pluck('max_id', 'canal_id');

        $noLeidosByCanal = [];
        foreach ($canalIds as $canalId) {
            $ultimoLeidoId = $lecturas->get($canalId, 0);
            $ultimoId = $ultimoMensajeIds->get($canalId, 0);
            $noLeidosByCanal[$canalId] = ($ultimoId > $ultimoLeidoId) ? ChatMensaje::where('canal_id', $canalId)->where('id', '>', $ultimoLeidoId)->count() : 0;
        }

        $this->canales = $canales->map(function ($canal) use ($user, $noLeidosByCanal) {
            $displayName = $canal->nombre;
            if ($canal->tipo === 'privado') {
                $other = $canal->miembros->first(fn ($m) => $m->user_id !== $user->id);
                $displayName = $other ? $other->user->name : $canal->nombre;
            }
            return [
                'id' => $canal->id,
                'nombre' => $displayName,
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

    public function loadMensajes()
    {
        if (! $this->activeChannelId) return;

        $messages = ChatMensaje::where('canal_id', $this->activeChannelId)
            ->when($this->highlight, fn ($q) => $q->where('contenido', 'like', "%{$this->highlight}%"))
            ->with(['user', 'menciones.user', 'replies.user', 'replies.menciones.user'])
            ->latest()->take(51)->get();

        $this->hasMoreMessages = $messages->count() > 50;
        $messages = $messages->take(50);

        $this->mensajes = $messages->reverse()->map(fn ($msg) => [
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
                'id' => $r->id, 'user_name' => $r->user->name,
                'user_avatar' => strtoupper(substr($r->user->name ?? 'U', 0, 2)),
                'contenido' => $r->contenido, 'created_at' => $r->created_at->format('H:i'),
            ])->toArray(),
            'attachments' => $msg->attachments,
            'edited' => ! is_null($msg->edited_at),
            'is_mine' => $msg->user_id === auth()->id(),
        ])->values()->toArray();

        $this->loadChannelMembers();
    }

    public function loadChannelMembers()
    {
        if (! $this->activeChannelId) { $this->channelMembers = []; return; }
        $canal = ChatCanal::find($this->activeChannelId);
        $this->channelMembers = $canal ? $canal->miembros()->with('user')->get()->map(fn ($m) => ['id' => $m->user->id, 'name' => $m->user->name])->toArray() : [];
    }

    public function loadMoreMessages()
    {
        if (! $this->activeChannelId || empty($this->mensajes)) return;
        $firstMessageId = $this->mensajes[0]['id'];
        $olderMessages = ChatMensaje::where('canal_id', $this->activeChannelId)
            ->where('id', '<', $firstMessageId)
            ->when($this->highlight, fn ($q) => $q->where('contenido', 'like', "%{$this->highlight}%"))
            ->with(['user', 'menciones.user', 'replies.user'])
            ->latest()->take(50)->get()->reverse()->map(fn ($msg) => [
                'id' => $msg->id, 'user_id' => $msg->user_id, 'user_name' => $msg->user->name,
                'user_avatar' => strtoupper(substr($msg->user->name ?? 'U', 0, 2)),
                'contenido' => $msg->contenido, 'created_at' => $msg->created_at->format('H:i'),
                'created_at_full' => $msg->created_at->diffForHumans(),
                'created_at_full_raw' => $msg->created_at->toIso8601String(),
                'parent_id' => $msg->parent_message_id,
                'replies' => $msg->replies->map(fn ($r) => ['id' => $r->id, 'user_name' => $r->user->name, 'user_avatar' => strtoupper(substr($r->user->name ?? 'U', 0, 2)), 'contenido' => $r->contenido, 'created_at' => $r->created_at->format('H:i')])->toArray(),
                'attachments' => $msg->attachments, 'edited' => !is_null($msg->edited_at), 'is_mine' => $msg->user_id === auth()->id(),
            ])->values()->toArray();
        $this->mensajes = array_merge($olderMessages, $this->mensajes);
        $this->hasMoreMessages = count($olderMessages) >= 50;
        $this->dispatch('preserve-scroll-position');
    }

    public function sendMessage()
    {
        if ((empty(trim($this->newMessage)) && empty($this->attachments)) || ! $this->activeChannelId) return;
        $canal = ChatCanal::find($this->activeChannelId);
        if (! $canal || ! Gate::allows('view', $canal)) return;

        $attachmentData = [];
        foreach ($this->attachments as $file) {
            $path = $file->storeAs('chat-attachments', time() . '_' . $file->getClientOriginalName(), 'public');
            $attachmentData[] = ['name' => $file->getClientOriginalName(), 'path' => $path, 'type' => $file->getMimeType(), 'size' => $file->getSize(), 'url' => Storage::disk('public')->url($path)];
        }

        $mensaje = ChatMensaje::create([
            'canal_id' => $this->activeChannelId, 'user_id' => auth()->id(),
            'parent_message_id' => $this->replyingTo,
            'contenido' => trim($this->newMessage) ?: '',
            'attachments' => ! empty($attachmentData) ? $attachmentData : null,
        ]);

        preg_match_all('/@(\w+)/', $this->newMessage, $matches);
        if (! empty($matches[1])) {
            foreach (User::whereIn('name', $matches[1])->get() as $user) {
                $mensaje->menciones()->create(['user_id' => $user->id]);
                if ($user->id !== auth()->id()) {
                    $user->notify(new \App\Notifications\Chat\NewChatMentionNotification($this->activeChannelId, $canal->nombre, auth()->user()->name, $mensaje->id));
                }
            }
        }

        if ($this->replyingTo) {
            $parent = ChatMensaje::find($this->replyingTo);
            if ($parent && $parent->user_id !== auth()->id()) {
                $parent->user->notify(new \App\Notifications\Chat\NewChatReplyNotification($this->activeChannelId, $canal->nombre, auth()->user()->name, $mensaje->id));
            }
        }

        $canal->miembros()->where('user_id', '!=', auth()->id())->each(function ($miembro) use ($canal, $mensaje) {
            $miembro->user->notify(new \App\Notifications\Chat\NewChatMessageNotification($canal->id, $canal->nombre, auth()->user()->name, $mensaje->id));
        });

        try { \App\Events\ChatMessageSent::dispatch($mensaje->load('user')); } catch (\Exception $e) {}

        $this->newMessage = '';
        $this->attachments = [];
        $this->replyingTo = null;
        $this->loadMensajes();
        $this->loadCanales();
        $this->marcarLeido();
        $this->dispatch('scroll-chat-to-bottom');
    }

    public function editMessage($messageId) {
        $m = ChatMensaje::find($messageId); if (!$m) return;
        if ($m->user_id !== auth()->id() && !auth()->user()->esAdmin()) return;
        $this->editingMessageId = $messageId;
        $this->editingContent = $m->contenido;
    }
    public function updateMessage() {
        if (!$this->editingMessageId) return;
        $m = ChatMensaje::find($this->editingMessageId); if (!$m) return;
        if ($m->user_id !== auth()->id() && !auth()->user()->esAdmin()) return;
        $t = trim($this->editingContent); if (empty($t)) return;
        $m->update(['contenido' => $t, 'edited_at' => now()]);
        $this->editingMessageId = null; $this->editingContent = '';
        $this->loadMensajes();
    }
    public function cancelEdit() { $this->editingMessageId = null; $this->editingContent = ''; }
    public function deleteMessage($messageId) {
        $m = ChatMensaje::find($messageId); if (!$m) return;
        if ($m->user_id !== auth()->id() && !auth()->user()->esAdmin()) return;
        $m->update(['contenido' => 'Este mensaje fue eliminado', 'edited_at' => null]);
        $this->loadMensajes(); $this->loadCanales();
    }

    public function marcarLeido()
    {
        if (! $this->activeChannelId) return;
        $ultimo = ChatMensaje::where('canal_id', $this->activeChannelId)->latest()->first();
        if (! $ultimo) return;
        ChatLectura::updateOrCreate(['canal_id' => $this->activeChannelId, 'user_id' => auth()->id()], ['ultimo_mensaje_leido_id' => $ultimo->id]);
        \App\Models\Chat\ChatMencion::where('user_id', auth()->id())->whereHas('mensaje', fn ($q) => $q->where('canal_id', $this->activeChannelId))->whereNull('leido_at')->update(['leido_at' => now()]);
        try { \App\Events\ChatMessageRead::dispatch($this->activeChannelId, auth()->id(), auth()->user()->name, $ultimo->id); } catch (\Exception $e) {}
        $this->loadCanales();
    }

    public function handleIncomingMessage($data) {
        if (!isset($data['canal_id']) || !$this->activeChannelId) return;
        if ((int)$data['canal_id'] === (int)$this->activeChannelId) { $this->loadMensajes(); }
        $this->loadCanales();
    }
    public function handleReadReceipt($data) { if (isset($data['canalId'])) $this->loadCanales(); }
    public function handleTyping($data) {
        if (!isset($data['canalId']) || (int)$data['canalId'] !== (int)$this->activeChannelId) return;
        if ((int)$data['userId'] === auth()->id()) return;
        $this->typingUsers[$data['userId']] = $data['userName'] ?? 'Alguien';
    }
    public function handleStopTyping($data) { if (isset($data['userId'])) unset($this->typingUsers[$data['userId']]); }

    public function searchUsers()
    {
        if (strlen($this->userSearch) < 2) { $this->dmUsers = []; return; }
        $this->dmUsers = User::where('status', 'active')->where('id', '!=', auth()->id())->where('name', 'like', "%{$this->userSearch}%")->orderBy('name')->limit(12)->get()->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'avatar' => strtoupper(substr($u->name ?? 'U', 0, 2))])->toArray();
    }

    public function startDm($userId)
    {
        $chatService = new ChatService();
        $canal = $chatService->createPrivateChannel(auth()->id(), (int)$userId);
        $this->loadCanales();
        $this->selectChannel($canal->id);
    }

    public function toggleChannelSection($tipo) {
        $idx = array_search($tipo, $this->expandedSections);
        if ($idx !== false) { unset($this->expandedSections[$idx]); $this->expandedSections = array_values($this->expandedSections); }
        else { $this->expandedSections[] = $tipo; }
    }

    public function removeAttachment($index) { if (isset($this->attachments[$index])) { unset($this->attachments[$index]); $this->attachments = array_values($this->attachments); } }
    public function updatedAttachments() { $this->validate(['attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar']); }

    public function searchGroupMembers()
    {
        if (strlen($this->groupSearch) < 2) {
            $this->groupSearchResults = [];
            return;
        }
        $this->groupSearchResults = User::where('status', 'active')
            ->where('id', '!=', auth()->id())
            ->where('name', 'like', "%{$this->groupSearch}%")
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'avatar' => strtoupper(substr($u->name ?? 'U', 0, 2)), 'selected' => in_array($u->id, $this->groupMemberIds)])
            ->toArray();
    }

    public function toggleGroupMember($userId)
    {
        $userId = (int) $userId;
        if (in_array($userId, $this->groupMemberIds)) {
            $this->groupMemberIds = array_values(array_diff($this->groupMemberIds, [$userId]));
        } else {
            $this->groupMemberIds[] = $userId;
        }
        $this->searchGroupMembers();
    }

    public function createGroup()
    {
        $this->validate([
            'groupName' => 'required|min:2|max:100',
        ], [
            'groupName.required' => 'El nombre del grupo es obligatorio.',
            'groupName.min' => 'El nombre debe tener al menos 2 caracteres.',
        ]);

        $chatService = new ChatService();
        $canal = $chatService->createGroup(
            nombre: $this->groupName,
            descripcion: $this->groupDescription ?: null,
            creadoPor: auth()->id(),
            memberIds: $this->groupMemberIds
        );

        $this->showCreateGroup = false;
        $this->groupName = '';
        $this->groupDescription = '';
        $this->groupMemberIds = [];
        $this->groupSearch = '';
        $this->groupSearchResults = [];
        $this->loadCanales();
        $this->selectChannel($canal->id);
    }

    public function render()
    {
        return view('livewire\chat.chat-index')->layout('components.layouts.app', ['fullWidth' => true]);
    }
}