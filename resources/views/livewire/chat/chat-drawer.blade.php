<div>
    {{-- DM creation modal --}}
    @if($showNewDm)
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showNewDm', false)">
        <div class="w-72 rounded-xl bg-white shadow-2xl" wire:click.stop>
            <div class="border-b border-slate-200 px-3 py-2.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-[13px] font-semibold text-slate-800">Nuevo mensaje directo</h3>
                    <button wire:click="$set('showNewDm', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="px-3 py-2.5">
                <div class="relative">
                    <svg class="absolute left-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        wire:model.live.debounce.300ms="dmSearch"
                        wire:change="searchDmUsers"
                        type="text"
                        placeholder="Buscar persona..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-7 pr-3 text-[12px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none"
                        autofocus
                    >
                </div>
                <div class="mt-2 max-h-40 overflow-y-auto">
                    @foreach($dmUsers as $user)
                    <button wire:click="startDm({{ $user['id'] }})" class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left hover:bg-gpt-50 transition-colors">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-[8px] font-semibold text-white">
                            {{ strtoupper(substr($user['name'], 0, 2)) }}
                        </span>
                        <span class="text-[12px] font-medium text-slate-700">{{ $user['name'] }}</span>
                    </button>
                    @endforeach
                    @if(strlen($dmSearch) >= 2 && empty($dmUsers))
                    <p class="py-2 text-center text-[11px] text-slate-400">Sin resultados</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Overlay backdrop --}}
    <div
        class="fixed inset-0 z-40 backdrop-blur-sm transition-opacity duration-300 {{ $open ? 'bg-slate-900/40 opacity-100' : 'bg-slate-900/0 opacity-0 pointer-events-none' }}"
        wire:click="closeDrawer"
    ></div>

    {{-- Drawer panel --}}
    <div
        class="fixed inset-y-0 right-0 z-50 flex w-full flex-col bg-white shadow-2xl transition-transform duration-300 ease-out sm:w-[480px] {{ $open ? 'translate-x-0' : 'translate-x-full' }}"
    >
        <div class="flex h-full flex-col">
            @if($activeChannelId)
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gpt-600 text-[10px] font-semibold text-white">
                            @php $activeChannel = collect($canales)->firstWhere('id', $activeChannelId); @endphp
                            {{ strtoupper(substr($activeChannel['nombre'] ?? 'C', 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-slate-800 truncate">
                                @if($activeChannel['tipo'] !== 'privado'){{ $activeChannel['nombre'] }}@else{{ collect($activeChannel['miembros'])->where('id', '!=', auth()->id())->pluck('name')->first() ?? $activeChannel['nombre'] }}@endif
                            </h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="/chat" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Abrir chat completo">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                        </a>
                        <button wire:click="closeDrawer" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Channel info bar --}}
                @if(!empty($activeChannel['miembros']))
                <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-1.5">
                    <div class="flex -space-x-1.5">
                        @foreach(array_slice($activeChannel['miembros'], 0, 5) as $miembro)
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gpt-600 text-[8px] font-semibold text-white ring-2 ring-white" title="{{ $miembro['name'] }}">
                            {{ $miembro['avatar'] }}
                        </span>
                        @endforeach
                    </div>
                    @if(count($activeChannel['miembros']) > 5)
                    <span class="text-[10px] text-slate-400">+{{ count($activeChannel['miembros']) - 5 }}</span>
                    @endif
                    <div class="relative ml-auto w-28 shrink-0">
                        <svg class="absolute left-2 top-1/2 h-3 w-3 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input
                            wire:model.live.debounce.200ms="highlight"
                            type="search"
                            placeholder="Buscar..."
                            class="w-full rounded-md border border-slate-200 bg-slate-50 py-0.5 pl-6 pr-2 text-[11px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none"
                        >
                    </div>
                </div>
                @endif

                {{-- Messages --}}
                <div
                    class="flex-1 overflow-y-auto px-3 py-2 space-y-1"
                    id="chat-messages-drawer"
                    x-data
                    x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })"
                >
                    {{-- Load more button --}}
                    @if($hasMoreMessages)
                    <div class="flex justify-center py-1 mb-1">
                        <button
                            wire:click="loadMoreMessages"
                            class="text-[11px] text-gpt-600 hover:text-gpt-700 hover:underline"
                        >
                            Cargar mensajes anteriores
                        </button>
                    </div>
                    @endif
                    @php
                        $lastDate = null;
                        $lastUserId = null;
                        $lastTimestamp = null;
                    @endphp

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
                        <div class="flex items-center gap-3 my-3">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <span class="text-[10px] font-medium text-slate-400">
                                @if($msgDate === now()->format('Y-m-d'))Hoy
                                @elseif($msgDate === now()->subDay()->format('Y-m-d'))Ayer
                                @else{{ now()->parse($msgDate)->isoFormat('D MMM') }}
                                @endif
                            </span>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>
                        @endif

                        @if($isGrouped)
                        <div class="group ml-7">
                            @if($editingMessageId === $msg['id'])
                            <div class="mb-1">
                                <form wire:submit="updateMessage" class="flex items-end gap-1">
                                    <div class="flex-1">
                                        <textarea wire:model.live="editingContent" rows="2" class="w-full resize-none rounded-md border border-gpt-600 bg-white px-2 py-1.5 text-[13px] text-slate-900 focus:outline-none focus:ring-1 focus:ring-gpt-600">{{ $editingContent }}</textarea>
                                    </div>
                                    <button type="submit" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gpt-600 text-white text-xs hover:bg-gpt-700">✓</button>
                                    <button type="button" wire:click="cancelEdit" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-slate-300 text-slate-500 text-xs hover:bg-slate-50">✕</button>
                                </form>
                            </div>
                            @elseif($isDeleted)
                            <p class="text-[12px] italic text-slate-400 px-2.5 py-1">Este mensaje fue eliminado</p>
                            @else
                            <div class="flex items-start gap-2 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                <div class="max-w-[85%]">
                                    <div class="rounded-lg px-2.5 py-1 {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-100 text-slate-700' }}">
                                        <p class="text-[13px] leading-relaxed whitespace-pre-wrap break-words">
                                            @php
                                            $texto = e($msg['contenido']);
                                            $texto = preg_replace('/@(\w+)/', '<span class="font-semibold '.($msg['is_mine'] ? 'text-gpt-200' : 'text-gpt-700').'">@$1</span>', $texto);
                                            if ($highlight) {
                                                $texto = preg_replace('/(' . preg_quote(e($highlight), '/') . ')/i', '<mark class="bg-yellow-200 text-slate-900 rounded px-0.5">$1</mark>', $texto);
                                            }
                                            echo $texto;
                                            @endphp
                                        </p>
                                        @if($msg['edited'])
                                        <span class="block text-[10px] {{ $msg['is_mine'] ? 'text-gpt-200' : 'text-slate-400' }}">(editado)</span>
                                        @endif
                                        @if(!empty($msg['attachments']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($msg['attachments'] as $att)
                                            @php $isImage = str_starts_with($att['type'] ?? '', 'image/'); @endphp
                                            <a href="{{ $att['url'] ?? '#' }}" target="_blank" class="block">
                                                @if($isImage)
                                                <img src="{{ $att['url'] }}" class="h-10 w-10 rounded object-cover border {{ $msg['is_mine'] ? 'border-white/20' : 'border-slate-300' }}" alt="{{ $att['name'] }}">
                                                @else
                                                <div class="flex items-center gap-1 rounded px-1.5 py-0.5 {{ $msg['is_mine'] ? 'bg-gpt-600/30' : 'bg-slate-200' }} text-[10px] {{ $msg['is_mine'] ? 'text-white' : 'text-slate-600' }}">
                                                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                    <span class="truncate max-w-[80px] hover:underline">{{ $att['name'] ?? 'archivo' }}</span>
                                                </div>
                                                @endif
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="ml-7 mt-0.5 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                @if(!$isDeleted)
                                <span class="text-[10px] text-slate-400">{{ $msg['created_at'] }}</span>
                                <button wire:click="$set('replyingTo', {{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Responder</button>
                                @if($canEdit)
                                <button wire:click="editMessage({{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Editar</button>
                                <button wire:click="deleteMessage({{ $msg['id'] }})" wire:confirm="Eliminar este mensaje?" class="text-[10px] text-slate-400 hover:text-red-500">Eliminar</button>
                                @endif
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="group mt-3 first:mt-0">
                            @if($editingMessageId === $msg['id'])
                            <div class="flex items-start gap-2 mb-1">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[9px] font-semibold">
                                    {{ $msg['user_avatar'] }}
                                </span>
                                <div class="flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-semibold {{ $msg['is_mine'] ? 'text-gpt-600' : 'text-slate-700' }}">{{ $msg['user_name'] }}</span>
                                    </div>
                                    <form wire:submit="updateMessage" class="mt-0.5 flex items-end gap-1">
                                        <div class="flex-1">
                                            <textarea wire:model.live="editingContent" rows="2" class="w-full resize-none rounded-md border border-gpt-600 bg-white px-2 py-1.5 text-[13px] text-slate-900 focus:outline-none focus:ring-1 focus:ring-gpt-600">{{ $editingContent }}</textarea>
                                        </div>
                                        <button type="submit" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gpt-600 text-white text-xs hover:bg-gpt-700">✓</button>
                                        <button type="button" wire:click="cancelEdit" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-slate-300 text-slate-500 text-xs hover:bg-slate-50">✕</button>
                                    </form>
                                </div>
                            </div>
                            @elseif($isDeleted)
                            <div class="flex items-start gap-2">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[9px] font-semibold">
                                    {{ $msg['user_avatar'] }}
                                </span>
                                <div>
                                    <span class="text-[11px] font-semibold {{ $msg['is_mine'] ? 'text-gpt-600' : 'text-slate-700' }}">{{ $msg['user_name'] }}</span>
                                    <p class="text-[12px] italic text-slate-400">Este mensaje fue eliminado</p>
                                </div>
                            </div>
                            @else
                            <div class="flex items-start gap-2 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-200 text-slate-600' }} text-[9px] font-semibold">
                                    {{ $msg['user_avatar'] }}
                                </span>
                                <div class="max-w-[85%]">
                                    <div class="flex items-center gap-1.5 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                        <span class="text-[11px] font-semibold {{ $msg['is_mine'] ? 'text-gpt-600' : 'text-slate-700' }}">{{ $msg['user_name'] }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $msg['created_at'] }}</span>
                                    </div>
                                    <div class="mt-0.5 rounded-lg px-2.5 py-1.5 {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-100 text-slate-700' }}">
                                        <p class="text-[13px] leading-relaxed whitespace-pre-wrap break-words">
                                            @php
                                            $texto = e($msg['contenido']);
                                            $texto = preg_replace('/@(\w+)/', '<span class="font-semibold '.($msg['is_mine'] ? 'text-gpt-200' : 'text-gpt-700').'">@$1</span>', $texto);
                                            if ($highlight) {
                                                $texto = preg_replace('/(' . preg_quote(e($highlight), '/') . ')/i', '<mark class="bg-yellow-200 text-slate-900 rounded px-0.5">$1</mark>', $texto);
                                            }
                                            echo $texto;
                                            @endphp
                                        </p>
                                        @if($msg['edited'])
                                        <span class="mt-0.5 block text-[10px] {{ $msg['is_mine'] ? 'text-gpt-200' : 'text-slate-400' }}">(editado)</span>
                                        @endif
                                        @if(!empty($msg['attachments']))
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            @foreach($msg['attachments'] as $att)
                                            @php $isImage = str_starts_with($att['type'] ?? '', 'image/'); @endphp
                                            <a href="{{ $att['url'] ?? '#' }}" target="_blank" class="block">
                                                @if($isImage)
                                                <img src="{{ $att['url'] }}" class="h-10 w-10 rounded object-cover border {{ $msg['is_mine'] ? 'border-white/20' : 'border-slate-300' }}" alt="{{ $att['name'] }}">
                                                @else
                                                <div class="flex items-center gap-1 rounded px-1.5 py-0.5 {{ $msg['is_mine'] ? 'bg-gpt-600/30' : 'bg-slate-200' }} text-[10px] {{ $msg['is_mine'] ? 'text-white' : 'text-slate-600' }}">
                                                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                    <span class="truncate max-w-[80px] hover:underline">{{ $att['name'] ?? 'archivo' }}</span>
                                                </div>
                                                @endif
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @if(!empty($msg['replies']))
                                    <div class="mt-1 ml-2 space-y-1 border-l-2 border-slate-200 pl-2">
                                        @foreach($msg['replies'] as $reply)
                                        <div class="text-[12px]">
                                            <span class="font-medium text-slate-600">{{ $reply['user_name'] }}</span>
                                            <span class="text-slate-500 ml-1">{{ $reply['contenido'] }}</span>
                                            <span class="text-[10px] text-slate-400 ml-1">{{ $reply['created_at'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            <div class="ml-8 mt-0.5 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                @if(!$isDeleted)
                                <button wire:click="$set('replyingTo', {{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Responder</button>
                                @if($canEdit)
                                <button wire:click="editMessage({{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">Editar</button>
                                <button wire:click="deleteMessage({{ $msg['id'] }})" wire:confirm="Eliminar este mensaje?" class="text-[10px] text-slate-400 hover:text-red-500">Eliminar</button>
                                @endif
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach

                    @empty($mensajes)
                    <div class="flex flex-1 flex-col items-center justify-center py-10 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                            <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-medium text-slate-400">Sin mensajes aún</p>
                        <p class="text-[12px] text-slate-400">Sé el primero en enviar un mensaje.</p>
                    </div>
                    @endempty

                    {{-- Skeleton loading --}}
                    <div wire:loading wire:target="selectChannel,loadMensajes,sendMessage">
                        @for($i = 0; $i < 4; $i++)
                        <div class="flex items-start gap-2 animate-pulse">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 rounded-full bg-slate-200"></span>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-2.5 w-20 rounded bg-slate-200"></div>
                                <div class="h-4 w-full max-w-xs rounded bg-slate-200"></div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Typing indicator --}}
                    @if(!empty($typingUsers))
                    <div class="flex items-center gap-1.5 px-1 py-1 text-[11px] text-slate-400">
                        <span class="flex gap-0.5">
                            <span class="h-1 w-1 animate-bounce rounded-full bg-gpt-500" style="animation-delay: 0ms"></span>
                            <span class="h-1 w-1 animate-bounce rounded-full bg-gpt-500" style="animation-delay: 150ms"></span>
                            <span class="h-1 w-1 animate-bounce rounded-full bg-gpt-500" style="animation-delay: 300ms"></span>
                        </span>
                        <span class="italic">{{ implode(', ', $typingUsers) }} escribiendo...</span>
                    </div>
                    @endif
                </div>

                {{-- Reply bar --}}
                @if($replyingTo)
                @php $replyMsg = collect($mensajes)->firstWhere('id', $replyingTo); @endphp
                @if($replyMsg)
                <div class="flex items-center gap-2 border-t border-slate-200 bg-slate-50 px-3 py-1.5">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l10.5-7L24 10"/></svg>
                    <span class="text-[11px] text-slate-500 truncate">Respondiendo a <strong class="text-slate-700">{{ $replyMsg['user_name'] }}</strong>: {{ Str::limit($replyMsg['contenido'], 40) }}</span>
                    <button wire:click="$set('replyingTo', null)" class="ml-auto shrink-0 text-slate-400 hover:text-slate-600">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endif
                @endif

                {{-- Input --}}
                <div class="relative border-t border-slate-200 px-3 py-2">
                    @if(count($attachments))
                    <div class="mb-2 flex flex-wrap gap-2">
                        @foreach($attachments as $i => $file)
                        <div class="relative group flex items-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5">
                            @if(str_starts_with($file->getMimeType(), 'image/'))
                            <img src="{{ $file->temporaryUrl() }}" class="h-8 w-8 rounded object-cover">
                            @else
                            <div class="flex h-8 w-8 items-center justify-center rounded bg-slate-200">
                                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            @endif
                            <span class="text-[10px] text-slate-500 truncate max-w-[80px]">{{ $file->getClientOriginalName() }}</span>
                            <button wire:click="removeAttachment({{ $i }})" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-gpt-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <form wire:submit="sendMessage" class="flex items-end gap-2" x-data="{ dragOver: false }"
                         x-on:dragover.prevent="dragOver = true"
                         x-on:dragleave.prevent="dragOver = false"
                         x-on:drop.prevent="dragOver = false; $el.closest('form').querySelector('input[type=file]').files = $event.dataTransfer.files; $el.closest('form').querySelector('input[type=file]').dispatchEvent(new Event('change'))"
                    >
                        <div x-show="dragOver" x-transition class="absolute inset-0 z-10 flex items-center justify-center rounded-xl border-2 border-dashed border-gpt-500 bg-gpt-50/80 pointer-events-none">
                            <div class="text-center">
                                <svg class="mx-auto h-6 w-6 text-gpt-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="mt-0.5 text-[11px] font-medium text-gpt-700">Soltar archivos</p>
                            </div>
                        </div>
                        <label class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <input type="file" wire:model="attachments" multiple id="chat-file-input-drawer" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                        </label>
                        <div class="flex-1" x-data="mentionAutocomplete()">
                            <textarea
                                id="chat-input-drawer"
                                x-ref="chatInput"
                                wire:model.live.debounce.200ms="newMessage"
                                wire:keydown.enter.prevent="sendMessage"
                                x-on:input="handleInput($event)"
                                x-on:keydown.down.prevent="navigateDown"
                                x-on:keydown.up.prevent="navigateUp"
                                x-on:keydown.enter.prevent="selectMention($event)"
                                rows="1"
                                placeholder="Escribe un mensaje..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gpt-200 transition-colors"
                            ></textarea>
                            <div
                                x-show="showDropdown && members.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute bottom-full left-0 mb-1 z-50 w-48 rounded-lg border border-slate-200 bg-white shadow-lg max-h-32 overflow-y-auto"
                                @click.outside="showDropdown = false"
                            >
                                <template x-for="(member, idx) in members" :key="member.id">
                                    <button
                                        x-on:click="insertMention(member.name)"
                                        :class="idx === selectedIndex ? 'bg-gpt-50 text-gpt-700' : 'text-slate-700 hover:bg-slate-50'"
                                        class="flex w-full items-center gap-1.5 px-2.5 py-1 text-[11px]"
                                    >
                                        <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-[7px] font-semibold text-white" x-text="member.name.substring(0, 2).toUpperCase()"></span>
                                        <span x-text="member.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gpt-600 text-white hover:bg-gpt-700 transition-colors shadow-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @else
                {{-- Channel list in drawer when no channel selected --}}
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-800">Chat</h2>
                    <div class="flex items-center gap-1">
                        <button wire:click="$set('showNewDm', true)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Nuevo mensaje directo">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <a href="/chat" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Abrir chat completo">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                        </a>
                        <button wire:click="closeDrawer" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-3 py-2">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Buscar canales..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-3 text-[12px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:bg-white focus:outline-none"
                        >
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    @foreach($canales as $canal)
                    <button
                        wire:click="selectChannel({{ $canal['id'] }})"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left transition-colors hover:bg-slate-50"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ (int)$activeChannelId === (int)$canal['id'] ? 'bg-gpt-600 text-white' : 'bg-slate-100 text-slate-500' }} text-[10px] font-semibold">
                            {{ strtoupper(substr($canal['nombre'], 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13px] font-medium {{ (int)$activeChannelId === (int)$canal['id'] ? 'text-gpt-700' : 'text-slate-700' }}">{{ $canal['nombre'] }}</span>
                            @if($canal['ultimo_mensaje'])
                            <span class="block truncate text-[11px] text-slate-400">{{ $canal['ultimo_mensaje']['user_name'] }}: {{ $canal['ultimo_mensaje']['contenido'] }}</span>
                            @endif
                        </span>
                        @if($canal['no_leidos'] > 0)
                        <span class="flex h-5 min-w-[20px] shrink-0 items-center justify-center rounded-full bg-gpt-600 px-1 text-[10px] font-semibold text-white">
                            {{ $canal['no_leidos'] > 99 ? '99+' : $canal['no_leidos'] }}
                        </span>
                        @endif
                    </button>
                    @endforeach

                    @if(empty($canales))
                    <div class="px-4 py-8 text-center">
                        <p class="text-[12px] text-slate-400">No hay canales disponibles</p>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scroll-chat-to-bottom', () => {
                requestAnimationFrame(() => {
                    const el = document.getElementById('chat-messages-drawer') || document.getElementById('chat-messages-page');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            });

            Livewire.on('preserve-scroll-position', () => {
                const el = document.getElementById('chat-messages-drawer') || document.getElementById('chat-messages-page');
                if (el) {
                    const prevHeight = el.scrollHeight;
                    requestAnimationFrame(() => {
                        el.scrollTop = el.scrollHeight - prevHeight + el.clientHeight;
                    });
                }
            });
        });
    </script>
</div>