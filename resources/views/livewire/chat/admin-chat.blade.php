<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-medium text-slate-800">Gestión de Chat</h1>
            <p class="text-sm text-slate-500">Administra canales, miembros y busca mensajes.</p>
        </div>
        <button wire:click="$toggle('showCreateForm')" class="rounded-md bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">
            + Nuevo canal
        </button>
    </div>

    {{-- Create / Edit Form --}}
    @if($editingChannel || ($showCreateForm ?? false))
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-3">{{ $editingChannel ? 'Editar canal' : 'Nuevo canal' }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-[13px] font-medium text-slate-600 mb-1">Nombre</label>
                <input wire:model="channelName" type="text" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-1 focus:ring-gpt-200">
                @error('channelName') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-600 mb-1">Tipo</label>
                <select wire:model="channelTipo" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none">
                    <option value="proyecto">Proyecto</option>
                    <option value="departamento">Departamento</option>
                    <option value="direccion">Dirección</option>
                    <option value="privado">Privado</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="block text-[13px] font-medium text-slate-600 mb-1">Miembros</label>
            <div class="max-h-48 overflow-y-auto flex flex-wrap gap-2 rounded-md border border-slate-200 p-3">
                @foreach($this->users as $user)
                <label class="flex items-center gap-1.5 rounded-md border border-slate-200 px-2 py-1 text-[12px] cursor-pointer hover:bg-slate-50">
                    <input type="checkbox" wire:model="selectedUserIds" value="{{ $user->id }}" class="rounded border-slate-300 text-gpt-600 focus:ring-gpt-500">
                    {{ $user->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button wire:click="saveChannel" class="rounded-md bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Guardar</button>
            <button wire:click="resetForm" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
        </div>
    </div>
    @endif

    {{-- Canales table --}}
    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.200ms="search" type="search" placeholder="Buscar canales..." class="w-full rounded-md border border-slate-200 py-1.5 pl-10 pr-3 text-sm focus:border-gpt-600 focus:outline-none">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wider text-slate-500">Canal</th>
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wider text-slate-500">Tipo</th>
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wider text-slate-500">Miembros</th>
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wider text-slate-500">Mensajes</th>
                        <th class="px-4 py-2 text-left text-[11px] font-medium uppercase tracking-wider text-slate-500">Último mensaje</th>
                        <th class="px-4 py-2 text-right text-[11px] font-medium uppercase tracking-wider text-slate-500">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->canales as $canal)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-2.5 text-[13px] font-medium text-slate-800">{{ $canal->nombre }}</td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium
                                {{ $canal->tipo === 'proyecto' ? 'bg-blue-50 text-blue-700' : '' }}
                                {{ $canal->tipo === 'departamento' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $canal->tipo === 'direccion' ? 'bg-purple-50 text-purple-700' : '' }}
                                {{ $canal->tipo === 'privado' ? 'bg-slate-100 text-slate-600' : '' }}">
                                {{ ucfirst($canal->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-[13px] text-slate-600">{{ $canal->miembros_count }}</td>
                        <td class="px-4 py-2.5 text-[13px] text-slate-600">{{ $canal->mensajes_count }}</td>
                        <td class="px-4 py-2.5 text-[12px] text-slate-400">
                            @if($canal->ultimoMensaje)
                            {{ $canal->ultimoMensaje->user->name ?? '' }} · {{ $canal->ultimoMensaje->created_at->diffForHumans() }}
                            @else
                            —
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <button wire:click="editChannel({{ $canal->id }})" class="text-[12px] font-medium text-gpt-600 hover:text-gpt-700 mr-2">Editar</button>
                            <button wire:click="confirmDeleteChannel({{ $canal->id }})" class="text-[12px] font-medium text-gpt-red-600 hover:text-gpt-red-700">Eliminar</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $this->canales->links() }}
        </div>
    </div>

    {{-- Delete Channel Confirmation Modal --}}
    @if($confirmingDeleteChannel)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('confirmingDeleteChannel', null)">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl" wire:click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Eliminar canal</h3>
                    <p class="text-[12px] text-slate-500 mt-0.5">Esta acción eliminará el canal y todos sus mensajes. No se puede deshacer.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button wire:click="$set('confirmingDeleteChannel', null)" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button wire:click="deleteChannel" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Eliminar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Message Confirmation Modal --}}
    @if($confirmingDeleteMessage)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('confirmingDeleteMessage', null)">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl" wire:click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Eliminar mensaje</h3>
                    <p class="text-[12px] text-slate-500 mt-0.5">El mensaje será marcado como eliminado y su contenido será reemplazado.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button wire:click="$set('confirmingDeleteMessage', null)" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                <button wire:click="deleteMessage" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Eliminar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Global message search --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-3">Búsqueda global de mensajes</h3>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="searchMessages" type="search" placeholder="Buscar en todos los mensajes..." class="w-full rounded-md border border-slate-200 py-1.5 pl-10 pr-3 text-sm focus:border-gpt-600 focus:outline-none">
        </div>

        @if($this->mensajesGlobal->isNotEmpty() && strlen($searchMessages) >= 2)
        <div class="mt-3 space-y-2 max-h-[400px] overflow-y-auto">
            @foreach($this->mensajesGlobal as $msg)
            <div class="rounded-md border border-slate-100 p-2.5 group">
                <div class="flex items-center gap-2">
                    <span class="text-[12px] font-medium text-slate-700">{{ $msg->user->name }}</span>
                    <span class="text-[11px] text-slate-400">en {{ $msg->canal->nombre }}</span>
                    <span class="text-[11px] text-slate-400 ml-auto">{{ $msg->created_at->diffForHumans() }}</span>
                    <button wire:click="confirmDeleteMessage({{ $msg->id }})" class="text-[11px] text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">Eliminar</button>
                </div>
                <p class="mt-1 text-[13px] text-slate-600">{{ $msg->contenido }}</p>
            </div>
            @endforeach
        </div>
        <div class="mt-3">
            {{ $this->mensajesGlobal->withQueryString()->links() }}
        </div>
        @elseif(strlen($searchMessages) >= 2)
        <p class="mt-3 text-center text-[13px] text-slate-400">No se encontraron mensajes.</p>
        @else
        <p class="mt-3 text-center text-[13px] text-slate-400">Escribe al menos 2 caracteres para buscar.</p>
        @endif
    </div>
</div>