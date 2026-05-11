<?php

namespace App\Livewire\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatMensaje;
use App\Models\User;
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
            ->get();
    }

    public function getMensajesGlobalProperty()
    {
        if (empty($this->searchMessages)) {
            return collect();
        }

        return ChatMensaje::with(['user', 'canal'])
            ->where('contenido', 'like', "%{$this->searchMessages}%")
            ->latest()
            ->take(50)
            ->get();
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
            if ($canal) {
                $canal->update([
                    'nombre' => $this->channelName,
                    'tipo' => $this->channelTipo,
                ]);

                // Sync members
                $existingIds = $canal->miembros()->pluck('user_id')->toArray();
                $toAdd = array_diff($this->selectedUserIds, $existingIds);
                $toRemove = array_diff($existingIds, $this->selectedUserIds);

                foreach ($toAdd as $uid) {
                    $canal->miembros()->create(['user_id' => $uid]);
                }
                $canal->miembros()->whereIn('user_id', $toRemove)->delete();
            }
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

    public function deleteChannel($id)
    {
        ChatCanal::find($id)?->delete();
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
