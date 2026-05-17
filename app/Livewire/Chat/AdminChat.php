<?php

namespace App\Livewire\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatMensaje;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class AdminChat extends Component
{
    use WithPagination;

    public $search = '';
    public $searchMessages = '';
    public $editingChannel = null;
    public $showCreateForm = false;
    public $channelName = '';
    public $channelTipo = 'departamento';
    public $channelUserId = null;
    public $selectedUserIds = [];
    public $confirmingDeleteChannel = null;
    public $confirmingDeleteMessage = null;

    protected $rules = [
        'channelName' => 'required|min:3',
        'channelTipo' => 'required|in:proyecto,departamento,direccion,privado',
        'selectedUserIds' => 'array',
    ];

    public function getUsersProperty()
    {
        return User::where('status', 'active')->orderBy('name')->get();
    }

    public function getCanalesProperty()
    {
        return ChatCanal::withCount(['mensajes', 'miembros'])
            ->with(['ultimoMensaje.user', 'miembros.user'])
            ->when($this->search, function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%");
            })
            ->orderByDesc('updated_at')
            ->paginate(15);
    }

    public function getMensajesGlobalProperty()
    {
        if (empty($this->searchMessages)) {
            return collect();
        }

        return ChatMensaje::with(['user', 'canal'])
            ->where('contenido', 'like', "%{$this->searchMessages}%")
            ->where('contenido', '!=', 'Este mensaje fue eliminado')
            ->latest()
            ->paginate(25);
    }

    public function editChannel($id)
    {
        $canal = ChatCanal::with('miembros')->find($id);
        if (! $canal) return;

        $this->editingChannel = $id;
        $this->channelName = $canal->nombre;
        $this->channelTipo = $canal->tipo;
        $this->selectedUserIds = $canal->miembros->pluck('user_id')->toArray();
    }

    public function saveChannel()
    {
        $this->validate();

        if ($this->editingChannel) {
            $canal = ChatCanal::find($this->editingChannel);
            if (! $canal || ! Gate::allows('update', $canal)) {
                return;
            }

            $canal->update([
                'nombre' => $this->channelName,
                'tipo' => $this->channelTipo,
            ]);

            $existingIds = $canal->miembros()->pluck('user_id')->toArray();
            $toAdd = array_diff($this->selectedUserIds, $existingIds);
            $toRemove = array_diff($existingIds, $this->selectedUserIds);

            foreach ($toAdd as $uid) {
                $canal->miembros()->create(['user_id' => $uid]);
            }
            $canal->miembros()->whereIn('user_id', $toRemove)->delete();
        } else {
            $canal = ChatCanal::create([
                'nombre' => $this->channelName,
                'tipo' => $this->channelTipo,
                'creado_por_id' => auth()->id(),
            ]);

            foreach ($this->selectedUserIds as $uid) {
                $canal->miembros()->create(['user_id' => $uid]);
            }
        }

        $this->resetForm();
    }

    public function confirmDeleteChannel($id)
    {
        $canal = ChatCanal::find($id);
        if (! $canal || ! Gate::allows('delete', $canal)) {
            return;
        }
        $this->confirmingDeleteChannel = $id;
    }

    public function deleteChannel()
    {
        $canal = ChatCanal::find($this->confirmingDeleteChannel);
        if (! $canal || ! Gate::allows('delete', $canal)) {
            $this->confirmingDeleteChannel = null;
            return;
        }
        $canal->mensajes()->delete();
        $canal->miembros()->delete();
        $canal->lecturas()->delete();
        $canal->delete();
        $this->confirmingDeleteChannel = null;
    }

    public function confirmDeleteMessage($id)
    {
        $msg = ChatMensaje::find($id);
        if (! $msg) return;
        $this->confirmingDeleteMessage = $id;
    }

    public function deleteMessage()
    {
        $msg = ChatMensaje::find($this->confirmingDeleteMessage);
        if (! $msg) {
            $this->confirmingDeleteMessage = null;
            return;
        }
        $msg->update(['contenido' => 'Este mensaje fue eliminado', 'edited_at' => null]);
        $this->confirmingDeleteMessage = null;
    }

    public function resetForm()
    {
        $this->editingChannel = null;
        $this->channelName = '';
        $this->channelTipo = 'departamento';
        $this->selectedUserIds = [];
    }

    public function render()
    {
        return view('livewire.chat.admin-chat')
            ->layout('components.layouts.app');
    }
}