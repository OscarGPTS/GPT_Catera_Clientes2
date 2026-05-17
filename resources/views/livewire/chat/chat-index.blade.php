<div class="relative flex h-[calc(100vh-64px)]" x-data="{ newDm: false }">
    {{-- Left sidebar: contacts + conversations --}}
    <div class="flex w-80 shrink-0 flex-col border-r border-slate-200 bg-white max-md:absolute max-md:inset-0 max-md:z-10 max-md:w-full max-md:border-r-0 {{ $activeChannelId ? 'max-md:hidden' : '' }}">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Chat</h2>
            <button wire:click="$set('showNewDm', true)" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-gpt-600 transition-colors" title="Nuevo mensaje">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>

        {{-- Tabs: Conversations / Contacts --}}
        <div class="flex border-b border-slate-200">
            <button wire:click="$set('tab', 'conversations')" class="flex-1 px-3 py-2 text-[12px] font-medium transition-colors {{ $tab === 'conversations' ? 'text-gpt-600 border-b-2 border-gpt-600' : 'text-slate-500 hover:text-slate-700' }}">
                Conversaciones
            </button>
            <button wire:click="$set('tab', 'contacts')" class="flex-1 px-3 py-2 text-[12px] font-medium transition-colors {{ $tab === 'contacts' ? 'text-gpt-600 border-b-2 border-gpt-600' : 'text-slate-500 hover:text-slate-700' }}">
                Contactos
            </button>
        </div>

        {{-- Search --}}
        <div class="px-3 py-2">
            @if($tab === 'contacts')
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="userSearch" wire:change="searchUsers" type="text" placeholder="Buscar personas..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none">
            </div>
            @else
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar en conversaciones..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none">
            </div>
            @endif
        </div>

        {{-- Content: Conversations or Contacts --}}
        <div class="flex-1 overflow-y-auto">
            @if($tab === 'contacts')
                {{-- Contacts list --}}
                @php
                    $allUsers = \App\Models\User::where('status', 'active')->where('id', '!=', auth()->id())
                        ->when(strlen($userSearch) >= 2, fn($q) => $q->where('name', 'like', "%{$userSearch}%"))
                        ->orderBy('name')->limit(30)->get();
                @endphp
                @foreach($allUsers as $user)
                <button wire:click="startDm({{ $user->id }})" class="flex w-full items-center gap-3 px-4 py-2.5 text-left hover:bg-slate-50 transition-colors">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-[12px] font-semibold text-white">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium text-slate-800">{{ $user->name }}</span>
                        <span class="block text-[11px] text-slate-400">{{ $user->email }}</span>
                    </div>
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </button>
                @endforeach
                @if($allUsers->isEmpty())
                <p class="py-8 text-center text-[12px] text-slate-400">No se encontraron personas</p>
                @endif
            @else
                {{-- Conversations list grouped by type --}}
                @php
                    $tipos = [
                        'privado' => ['label' => 'Mensajes directos', 'icon' => 'user'],
                        'proyecto' => ['label' => 'Proyectos', 'icon' => 'clipboard-document-list'],
                        'departamento' => ['label' => 'Departamentos', 'icon' => 'building-office'],
                        'direccion' => ['label' => 'Dirección', 'icon' => 'star'],
                    ];
                    $grouped = collect($canales)->groupBy('tipo');
                @endphp

                @foreach($tipos as $tipo => $info)
                    @php $channels = $grouped->get($tipo, []); @endphp
                    @if(count($channels) > 0)
                    <div class="mb-0.5">
                        <button wire:click="toggleChannelSection('{{ $tipo }}')" class="flex w-full items-center gap-1 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-300">
                            <svg class="h-3 w-3 transition-transform {{ in_array($tipo, $expandedSections) ? '' : '-rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            {{ $info['label'] }}
                        </button>
                        @if(in_array($tipo, $expandedSections))
                        <div class="space-y-0.5 px-1">
                            @foreach($channels as $canal)
                            <button wire:click="selectChannel({{ $canal['id'] }})" class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors {{ (int)$activeChannelId === (int)$canal['id'] ? 'bg-gpt-50 border border-gpt-200' : 'hover:bg-slate-50 border border-transparent' }}">
                                @if($tipo === 'privado')
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ (int)$activeChannelId === (int)$canal['id'] ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-600' }} text-[12px] font-semibold">
                                    {{ strtoupper(substr($canal['nombre'], 0, 2)) }}
                                </div>
                                @else
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ (int)$activeChannelId === (int)$canal['id'] ? 'bg-gpt-600 text-white' : 'bg-slate-100 text-slate-500' }} text-[12px] font-semibold">
                                    #
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-medium {{ (int)$activeChannelId === (int)$canal['id'] ? 'text-gpt-700' : 'text-slate-800' }}">{{ $canal['nombre'] }}</span>
                                    @if($canal['ultimo_mensaje'] && (int)$activeChannelId !== (int)$canal['id'])
                                    <span class="block truncate text-[11px] text-slate-400">{{ $canal['ultimo_mensaje']['user_name'] }}: {{ $canal['ultimo_mensaje']['contenido'] }}</span>
                                    @endif
                                </div>
                                @if($canal['no_leidos'] > 0 && (int)$activeChannelId !== (int)$canal['id'])
                                <span class="flex h-5 min-w-[20px] shrink-0 items-center justify-center rounded-full bg-gpt-600 px-1 text-[10px] font-semibold text-white">
                                    {{ $canal['no_leidos'] > 99 ? '99+' : $canal['no_leidos'] }}
                                </span>
                                @endif
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                @endforeach

                @if(empty($canales))
                <div class="flex flex-col items-center justify-center px-6 py-12">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="mt-3 text-[13px] font-medium text-slate-400">Sin conversaciones</p>
                    <p class="mt-1 text-[12px] text-slate-400">Inicia un chat desde la pestaña Contactos</p>
                </div>
                @endif
            @endif
        </div>

        {{-- User footer --}}
        <div class="border-t border-slate-200 px-4 py-3">
            <div class="flex items-center gap-2.5">
                <div class="relative shrink-0">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-gpt-500 to-gpt-700 text-[12px] font-semibold text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5 rounded-full border-2 border-white bg-green-500"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-medium text-slate-800">{{ auth()->user()->name ?? 'Usuario' }}</p>
                    <p class="text-[10px] text-slate-400">En línea</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel: Messages area (reuse same structure as chat-panel) --}}
    <div class="flex flex-1 flex-col bg-slate-50 max-md:absolute max-md:inset-0 max-md:z-10 max-md:w-full {{ $activeChannelId ? '' : 'max-md:hidden' }}">
        @if($activeChannelId)
            @php $activeChannel = collect($canales)->firstWhere('id', $activeChannelId); @endphp

            {{-- Channel header --}}
            <div class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <button wire:click="$set('activeChannelId', null)" class="mr-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 md:hidden" title="Volver">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        @if($activeChannel['tipo'] === 'privado')
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-[12px] font-semibold text-white">
                            {{ strtoupper(substr($activeChannel['nombre'], 0, 2)) }}
                        </div>
                        @else
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gpt-600 text-[12px] font-semibold text-white">#</span>
                        @endif
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-semibold text-slate-800">{{ $activeChannel['nombre'] }}</h3>
                            @if($activeChannel['descripcion'])
                            <p class="truncate text-[11px] text-slate-400">{{ $activeChannel['descripcion'] }}</p>
                            @endif
                        </div>
                    </div>
                    @if(!empty($activeChannel['miembros']))
                    <div class="mt-1.5 flex items-center gap-1.5">
                        <div class="flex -space-x-1.5">
                            @foreach(collect($activeChannel['miembros'])->slice(0, 6) as $miembro)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gpt-600 text-[8px] font-semibold text-white ring-2 ring-white" title="{{ $miembro['name'] }}">
                                {{ $miembro['avatar'] }}
                            </span>
                            @endforeach
                        </div>
                        @if(count($activeChannel['miembros']) > 6)
                        <span class="text-[11px] text-slate-400">+{{ count($activeChannel['miembros']) - 6 }} más</span>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <div class="relative w-44">
                        <svg class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input wire:model.live.debounce.300ms="highlight" type="search" placeholder="Buscar en mensajes..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-3 text-[12px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gpt-200">
                    </div>
                </div>
            </div>

            {{-- Messages area --}}
            <div
                class="flex-1 overflow-y-auto px-5 py-4"
                id="chat-messages-page"
                x-data
                x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })"
            >
                @if($hasMoreMessages)
                <div class="flex justify-center py-2 mb-2">
                    <button wire:click="loadMoreMessages" class="text-[12px] text-gpt-600 hover:text-gpt-700 hover:underline">Cargar mensajes anteriores</button>
                </div>
                @endif

                @php $lastDate = null; $lastUserId = null; $lastTimestamp = null; @endphp

                @foreach($mensajes as $msg)
                    @php
                        $msgDate = \Carbon\Carbon::parse($msg['created_at_full_raw'] ?? now())->format('Y-m-d');
                        $showDate = $lastDate !== $msgDate;
                        $lastDate = $msgDate;
                        $isGrouped = !$showDate && $lastUserId === $msg['user_id'] && $lastTimestamp && (now()->parse($msg['created_at'])->diffInMinutes($lastTimestamp) < 5);
                        $lastUserId = $msg['user_id'];
                        $lastTimestamp = now()->parse($msg['created_at']);
                        $canEdit = $msg['is_mine'] || auth()->user()->esAdmin();
                        $isDeleted = $msg['contenido'] === 'Este mensaje fue eliminado';
                    @endphp

                    @if($showDate)
                    <div class="flex items-center gap-3 my-4">
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <span class="text-[11px] font-medium text-slate-400">
                            @if($msgDate === now()->format('Y-m-d'))Hoy
                            @elseif($msgDate === now()->subDay()->format('Y-m-d'))Ayer
                            @else{{ now()->parse($msgDate)->isoFormat('D MMM YYYY') }}
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-slate-200"></div>
                    </div>
                    @endif

                    @if($isGrouped)
                    <div class="group ml-9">
                        @if($editingMessageId === $msg['id'])
                        <div class="mb-1">
                            <form wire:submit="updateMessage" class="flex items-end gap-2">
                                <div class="flex-1"><textarea wire:model.live="editingContent" rows="2" class="w-full resize-none rounded-lg border border-gpt-600 bg-white px-3 py-2 text-[13px] text-slate-900 focus:outline-none focus:ring-1 focus:ring-gpt-600">{{ $editingContent }}</textarea></div>
                                <button type="submit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gpt-600 text-white hover:bg-gpt-700">✓</button>
                                <button type="button" wire:click="cancelEdit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-300 text-slate-500 hover:bg-slate-50">✕</button>
                            </form>
                        </div>
                        @elseif($isDeleted)
                        <p class="text-[12px] italic text-slate-400">Este mensaje fue eliminado</p>
                        @else
                        <div class="flex items-start gap-2 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                            <div class="max-w-[70%]">
                                <div class="rounded-lg px-3 py-1.5 {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                                    <p class="text-[13px] leading-relaxed whitespace-pre-wrap break-words">{!! preg_replace('/@(\w+)/', '<span class="font-semibold '.($msg['is_mine'] ? 'text-gpt-200' : 'text-gpt-600').'">@$1</span>', e($msg['contenido'])) !!}</p>
                                    @if($msg['edited'])<span class="block text-[10px] {{ $msg['is_mine'] ? 'text-gpt-200' : 'text-slate-400' }}">(editado)</span>@endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="ml-9 mt-0.5 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                            <span class="text-[10px] text-slate-400">{{ $msg['created_at'] }}</span>
                            @if(!$isDeleted)
                            <button wire:click="$set('replyingTo', {{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Responder</button>
                            @if($canEdit)
                            <button wire:click="editMessage({{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Editar</button>
                            <button wire:click="deleteMessage({{ $msg['id'] }})" wire:confirm="Eliminar?" class="text-[10px] text-slate-400 hover:text-red-500">Eliminar</button>
                            @endif
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="group mt-4 first:mt-0">
                        @if($editingMessageId === $msg['id'])
                        <div class="flex items-start gap-2.5 mb-2">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[11px] font-semibold">{{ $msg['user_avatar'] }}</span>
                            <div class="flex-1">
                                <div class="flex items-baseline gap-2 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                    <span class="text-[12px] font-semibold {{ $msg['is_mine'] ? 'text-gpt-600' : 'text-slate-700' }}">{{ $msg['user_name'] }}</span>
                                </div>
                                <form wire:submit="updateMessage" class="mt-1 flex items-end gap-2">
                                    <div class="flex-1"><textarea wire:model.live="editingContent" rows="2" class="w-full resize-none rounded-lg border border-gpt-600 bg-white px-3 py-2 text-[13px] text-slate-900 focus:outline-none focus:ring-1 focus:ring-gpt-600">{{ $editingContent }}</textarea></div>
                                    <button type="submit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gpt-600 text-white hover:bg-gpt-700">✓</button>
                                    <button type="button" wire:click="cancelEdit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-300 text-slate-500 hover:bg-slate-50">✕</button>
                                </form>
                            </div>
                        </div>
                        @elseif($isDeleted)
                        <div class="flex items-start gap-2.5">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[11px] font-semibold">{{ $msg['user_avatar'] }}</span>
                            <div><span class="text-[12px] font-semibold {{ $msg['is_mine'] ? 'text-gpt-600' : 'text-slate-700' }}">{{ $msg['user_name'] }}</span><p class="text-[12px] italic text-slate-400">Este mensaje fue eliminado</p></div>
                        </div>
                        @else
                        <div class="flex items-start gap-2.5 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[11px] font-semibold">{{ $msg['user_avatar'] }}</span>
                            <div class="max-w-[70%]">
                                <div class="flex items-baseline gap-2 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                    <span class="text-[12px] font-semibold {{ $msg['is_mine'] ? 'text-gpt-600' : 'text-slate-700' }}">{{ $msg['user_name'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $msg['created_at'] }}</span>
                                </div>
                                <div class="mt-1 rounded-lg px-3 py-2 {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                                    <p class="text-[13px] leading-relaxed whitespace-pre-wrap break-words">{!! preg_replace('/@(\w+)/', '<span class="font-semibold '.($msg['is_mine'] ? 'text-gpt-200' : 'text-gpt-600').'">@$1</span>', e($msg['contenido'])) !!}</p>
                                    @if($msg['edited'])<span class="block text-[10px] {{ $msg['is_mine'] ? 'text-gpt-200' : 'text-slate-400' }}">(editado)</span>@endif
                                    @if(!empty($msg['attachments']))
                                    <div class="mt-1.5 flex flex-wrap gap-2">
                                        @foreach($msg['attachments'] as $att)
                                        @php $isImage = str_starts_with($att['type'] ?? '', 'image/'); @endphp
                                        <a href="{{ $att['url'] ?? '#' }}" target="_blank" class="block">
                                            @if($isImage)<img src="{{ $att['url'] }}" class="h-16 w-16 rounded-lg object-cover border {{ $msg['is_mine'] ? 'border-white/20' : 'border-slate-200' }}" alt="{{ $att['name'] }}">
                                            @else<div class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 {{ $msg['is_mine'] ? 'bg-gpt-600/30 text-white' : 'bg-slate-100 text-slate-600' }} text-[11px]"><svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg><span class="hover:underline">{{ $att['name'] ?? 'archivo' }}</span></div>
                                            @endif
                                        </a>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="ml-10.5 mt-0.5 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                            @if(!$isDeleted)
                            <button wire:click="$set('replyingTo', {{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Responder</button>
                            @if($canEdit)
                            <button wire:click="editMessage({{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Editar</button>
                            <button wire:click="deleteMessage({{ $msg['id'] }})" wire:confirm="Eliminar?" class="text-[10px] text-slate-400 hover:text-red-500">Eliminar</button>
                            @endif
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach

                @empty($mensajes)
                <div class="flex flex-1 flex-col items-center justify-center py-16">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100"><svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
                    <p class="mt-4 text-sm font-medium text-slate-400">Sin mensajes aún</p>
                    <p class="mt-1 text-[12px] text-slate-400">Sé el primero en enviar un mensaje.</p>
                </div>
                @endempty

                @if(!empty($typingUsers))
                <div class="flex items-center gap-2 px-1 py-2 text-[12px] text-slate-400">
                    <span class="flex gap-0.5"><span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gpt-500" style="animation-delay: 0ms"></span><span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gpt-500" style="animation-delay: 150ms"></span><span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gpt-500" style="animation-delay: 300ms"></span></span>
                    <span class="italic">{{ implode(', ', $typingUsers) }} escribiendo...</span>
                </div>
                @endif
            </div>

            {{-- Reply bar --}}
            @if($replyingTo)
            @php $replyMsg = collect($mensajes)->firstWhere('id', $replyingTo); @endphp
            @if($replyMsg)
            <div class="flex items-center gap-2 border-t border-slate-200 bg-slate-50 px-5 py-2">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l10.5-7L24 10M7 13v8a2 2 0 002 2h6a2 2 0 002-2v-8"/></svg>
                <span class="text-[12px] text-slate-500">Respondiendo a <strong class="text-slate-700">{{ $replyMsg['user_name'] }}</strong>: {{ Str::limit($replyMsg['contenido'], 60) }}</span>
                <button wire:click="$set('replyingTo', null)" class="ml-auto text-slate-400 hover:text-slate-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            @endif
            @endif

            {{-- Input area --}}
            <div class="relative border-t border-slate-200 bg-white px-5 py-3">
                @if(count($attachments))
                <div class="mb-2 flex flex-wrap gap-2">
                    @foreach($attachments as $i => $file)
                    <div class="relative group flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                        @if(str_starts_with($file->getMimeType(), 'image/'))
                        <img src="{{ $file->temporaryUrl() }}" class="h-10 w-10 rounded object-cover">
                        @else
                        <div class="flex h-10 w-10 items-center justify-center rounded bg-slate-200"><svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>
                        @endif
                        <span class="text-[11px] text-slate-600 truncate max-w-[100px]">{{ $file->getClientOriginalName() }}</span>
                        <button wire:click="removeAttachment({{ $i }})" class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-gpt-red-500 text-white shadow-sm opacity-0 group-hover:opacity-100 transition-opacity"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    @endforeach
                </div>
                @endif
                <form wire:submit="sendMessage" class="flex items-end gap-2.5" x-data="{ dragOver: false }" x-on:dragover.prevent="dragOver = true" x-on:dragleave.prevent="dragOver = false" x-on:drop.prevent="dragOver = false; $el.closest('form').querySelector('input[type=file]').files = $event.dataTransfer.files; $el.closest('form').querySelector('input[type=file]').dispatchEvent(new Event('change'))">
                    <div x-show="dragOver" x-transition class="absolute inset-0 z-10 flex items-center justify-center rounded-xl border-2 border-dashed border-gpt-500 bg-gpt-50/80 pointer-events-none">
                        <div class="text-center"><svg class="mx-auto h-8 w-8 text-gpt-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg><p class="mt-1 text-sm font-medium text-gpt-700">Soltar archivos aquí</p></div>
                    </div>
                    <label class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <input type="file" wire:model="attachments" multiple class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                    </label>
                    <div class="flex-1 relative" x-data="mentionAutocomplete()">
                        <textarea id="chat-input" x-ref="chatInput" wire:model.live.debounce.200ms="newMessage" wire:keydown.enter.prevent="sendMessage" x-on:input="handleInput($event)" x-on:keydown.down.prevent="navigateDown" x-on:keydown.up.prevent="navigateUp" x-on:keydown.enter.prevent="selectMention($event)" rows="1" placeholder="Escribe un mensaje... (@usuario para mencionar)" class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gpt-200 transition-colors"></textarea>
                        <div x-show="showDropdown && members.length > 0" x-transition class="absolute bottom-full left-0 mb-1 z-50 w-56 rounded-lg border border-slate-200 bg-white shadow-lg max-h-40 overflow-y-auto" @click.outside="showDropdown = false">
                            <template x-for="(member, idx) in members" :key="member.id"><button x-on:click="insertMention(member.name)" :class="idx === selectedIndex ? 'bg-gpt-50 text-gpt-700' : 'text-slate-700 hover:bg-slate-50'" class="flex w-full items-center gap-2 px-3 py-1.5 text-[12px]"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-[8px] font-semibold text-white" x-text="member.name.substring(0,2).toUpperCase()"></span><span x-text="member.name"></span></button></template>
                        </div>
                    </div>
                    <button type="submit" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gpt-600 text-white hover:bg-gpt-700 transition-colors shadow-sm" title="Enviar mensaje"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg></button>
                </form>
            </div>
        @else
            {{-- Empty state --}}
            <div class="flex flex-1 flex-col items-center justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100"><svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
                <h3 class="mt-4 text-sm font-semibold text-slate-600">Selecciona una conversación</h3>
                <p class="mt-1 text-[13px] text-slate-400">Elige un chat en la barra lateral o inicia uno nuevo desde Contactos.</p>
            </div>
        @endif
    </div>

    {{-- New DM modal --}}
    @if($showNewDm)
    <div class="absolute inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showNewDm', false)">
        <div class="w-80 rounded-xl bg-white shadow-2xl" wire:click.stop>
            <div class="border-b border-slate-200 px-4 py-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800">Nuevo mensaje directo</h3>
                    <button wire:click="$set('showNewDm', false)" class="text-slate-400 hover:text-slate-600"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            </div>
            <div class="px-4 py-3">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="dmSearch" wire:change="searchDmUsers" type="text" placeholder="Buscar persona..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none" autofocus>
                </div>
                <div class="mt-2 max-h-48 overflow-y-auto">
                    @foreach($dmUsers as $user)
                    <button wire:click="startDm({{ $user['id'] }})" class="flex w-full items-center gap-2.5 rounded-lg px-2 py-1.5 text-left hover:bg-gpt-50 transition-colors">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-[9px] font-semibold text-white">{{ strtoupper(substr($user['name'], 0, 2)) }}</span>
                        <span class="text-[13px] font-medium text-slate-700">{{ $user['name'] }}</span>
                    </button>
                    @endforeach
                    @if(strlen($dmSearch) >= 2 && empty($dmUsers))
                    <p class="py-3 text-center text-[12px] text-slate-400">Sin resultados</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scroll-chat-to-bottom', () => {
                requestAnimationFrame(() => {
                    const el = document.getElementById('chat-messages-page');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            });
            Livewire.on('preserve-scroll-position', () => {
                const el = document.getElementById('chat-messages-page');
                if (el) {
                    const prevHeight = el.scrollHeight;
                    requestAnimationFrame(() => { el.scrollTop = el.scrollHeight - prevHeight + el.clientHeight; });
                }
            });
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('mentionAutocomplete', () => ({
                showDropdown: false, selectedIndex: 0, members: [], atPosition: -1, query: '',
                handleInput(event) {
                    const textarea = event.target; const value = textarea.value; const cursorPos = textarea.selectionStart;
                    const textBeforeCursor = value.substring(0, cursorPos); const atIndex = textBeforeCursor.lastIndexOf('@');
                    if (atIndex !== -1) { const textAfterAt = textBeforeCursor.substring(atIndex + 1);
                        if (textAfterAt.length <= 30 && !textAfterAt.includes(' ') && !textAfterAt.includes('\n')) {
                            this.query = textAfterAt.toLowerCase(); this.atPosition = atIndex;
                            const component = Livewire.getByName('chat.chat-index');
                            if (component) { this.members = (component.channelMembers || []).filter(m => m.name.toLowerCase().includes(this.query)); }
                            this.showDropdown = this.members.length > 0; this.selectedIndex = 0; return;
                    } } this.showDropdown = false;
                },
                navigateDown() { if (this.selectedIndex < this.members.length - 1) this.selectedIndex++; },
                navigateUp() { if (this.selectedIndex > 0) this.selectedIndex--; },
                selectMention(event) {
                    if (this.showDropdown && this.members.length > 0) { event.preventDefault(); event.stopPropagation(); this.insertMention(this.members[this.selectedIndex].name); return; }
                    this.$wire.sendMessage();
                },
                insertMention(name) {
                    const textarea = this.$refs.chatInput || document.getElementById('chat-input');
                    if (!textarea) return;
                    const before = textarea.value.substring(0, this.atPosition); const after = textarea.value.substring(textarea.selectionStart);
                    this.$wire.newMessage = before + '@' + name + ' ' + after;
                    this.showDropdown = false;
                    this.$nextTick(() => { const newPos = this.atPosition + name.length + 2; textarea.setSelectionRange(newPos, newPos); textarea.focus(); });
                }
            }));
        });
    </script>
</div>