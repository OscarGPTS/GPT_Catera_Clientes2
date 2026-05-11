<div>
    {{-- Overlay backdrop --}}
    <div
        x-show="$wire.open"
        x-transition.opacity
        x-on:click="$wire.closeDrawer()"
        class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm"
        style="display: none;"
        x-cloak
    ></div>

    {{-- Drawer panel --}}
    <div
        x-show="$wire.open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full flex-col bg-white shadow-xl sm:w-[480px]"
        style="display: none;"
        x-cloak
        x-trap="$wire.open"
    >
        <div class="flex h-full flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-800">Chat</h2>
                <button
                    x-on:click="$wire.closeDrawer()"
                    class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body: split channels + messages --}}
            <div class="flex flex-1 overflow-hidden">
                {{-- Channel list --}}
                <div class="flex w-[180px] shrink-0 flex-col border-r border-slate-100 bg-slate-50">
                    <div class="border-b border-slate-200 px-3 py-2">
                        <div class="relative">
                            <svg class="absolute left-2 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                wire:model.live.debounce.200ms="search"
                                type="search"
                                placeholder="Buscar..."
                                class="w-full rounded-md border border-slate-200 bg-white py-1 pl-7 pr-2 text-[12px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none"
                            >
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto">
                        @php
                        $tipos = ['proyecto' => 'Proyectos', 'departamento' => 'Deptos', 'direccion' => 'Dirección', 'privado' => 'Privados'];
                        @endphp
                        @foreach($tipos as $tipoKey => $tipoLabel)
                            @php $canalesTipo = array_filter($canales, fn($c) => $c['tipo'] === $tipoKey); @endphp
                            @if(count($canalesTipo))
                            <div class="px-3 py-1.5">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $tipoLabel }}</p>
                            </div>
                            @foreach($canalesTipo as $canal)
                            <button
                                wire:click="selectChannel({{ $canal['id'] }})"
                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-[13px] transition-colors {{ $activeChannelId === $canal['id'] ? 'bg-gpt-50 text-gpt-700' : 'text-slate-600 hover:bg-slate-100' }}"
                            >
                                <span class="truncate flex-1">{{ $canal['nombre'] }}</span>
                                @if($canal['no_leidos'] > 0)
                                <span class="flex h-4 min-w-[16px] items-center justify-center rounded-full bg-gpt-600 px-1 text-[10px] font-semibold text-white">
                                    {{ $canal['no_leidos'] > 99 ? '99+' : $canal['no_leidos'] }}
                                </span>
                                @endif
                            </button>
                            @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Message area --}}
                <div
                    class="flex flex-1 flex-col"
                    x-data="{ dragOver: false }"
                    x-on:dragover.prevent="dragOver = true"
                    x-on:dragleave.prevent="dragOver = false"
                    x-on:drop.prevent="dragOver = false; if($event.dataTransfer.files.length) { const input = document.getElementById('chat-file-input'); const dt = new DataTransfer(); for(let f of $event.dataTransfer.files) dt.items.add(f); input.files = dt.files; input.dispatchEvent(new Event('change')) }"
                >
                    {{-- Drop zone overlay --}}
                    <div x-show="dragOver" x-transition class="absolute inset-0 z-10 flex items-center justify-center bg-gpt-600/20 backdrop-blur-sm">
                        <div class="rounded-xl bg-white/90 px-6 py-4 shadow-lg text-center">
                            <svg class="mx-auto h-8 w-8 text-gpt-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <p class="mt-2 text-sm font-medium text-slate-700">Suelta los archivos aquí</p>
                            <p class="text-[11px] text-slate-500">Imágenes, PDF, documentos, ZIP</p>
                        </div>
                    </div>
                    @if($activeChannelId)
                        @php $activeChannel = collect($canales)->firstWhere('id', $activeChannelId); @endphp
                        {{-- Channel header --}}
                        <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2">
                            <div>
                                <p class="text-[13px] font-medium text-slate-800">{{ $activeChannel['nombre'] ?? 'Canal' }}</p>
                                @if(!empty($activeChannel['miembros']))
                                <div class="flex items-center gap-1 mt-0.5">
                                    <div class="flex -space-x-1.5">
                                        @foreach(array_slice($activeChannel['miembros'], 0, 4) as $miembro)
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-gpt-600 text-[9px] font-semibold text-white ring-1 ring-white" title="{{ $miembro['name'] }}">
                                            {{ $miembro['avatar'] }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @if(count($activeChannel['miembros']) > 4)
                                    <span class="text-[10px] text-slate-400">+{{ count($activeChannel['miembros']) - 4 }}</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Messages --}}
                        <div class="flex-1 overflow-y-auto px-3 py-2 space-y-3 relative" id="chat-messages" x-data x-init="$nextTick(() => { var el = document.getElementById('chat-messages'); if(el) el.scrollTop = el.scrollHeight; })">
                            @foreach($mensajes as $msg)
                            <div class="group">
                                <div class="flex items-start gap-2 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[9px] font-semibold text-slate-600">
                                        {{ $msg['user_avatar'] }}
                                    </span>
                                    <div class="max-w-[85%]">
                                        <div class="flex items-center gap-1.5 {{ $msg['is_mine'] ? 'flex-row-reverse' : '' }}">
                                            <span class="text-[11px] font-medium text-slate-600">{{ $msg['user_name'] }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $msg['created_at'] }}</span>
                                        </div>
                                        <div class="mt-0.5 rounded-lg px-2.5 py-1.5 {{ $msg['is_mine'] ? 'bg-gpt-500 text-white' : 'bg-slate-100 text-slate-700' }}">
                                            <p class="text-[13px] leading-relaxed whitespace-pre-wrap break-words">
                                                {!! preg_replace('/@(\w+)/', '<span class="font-semibold text-gpt-300 '.($msg['is_mine'] ? '' : 'text-gpt-700').'">@$1</span>', e($msg['contenido'])) !!}
                                            </p>
                                            @if($msg['edited'])
                                            <span class="mt-0.5 block text-[10px] {{ $msg['is_mine'] ? 'text-gpt-200' : 'text-slate-400' }}">(editado)</span>
                                            @endif
                                            {{-- Attachments --}}
                                            @if(!empty($msg['attachments']))
                                            <div class="mt-1.5 flex flex-wrap gap-1">
                                                @foreach($msg['attachments'] as $att)
                                                @php $isImage = str_starts_with($att['type'] ?? '', 'image/'); @endphp
                                                <a href="{{ $att['url'] ?? '#' }}" target="_blank" class="block">
                                                    @if($isImage)
                                                    <img src="{{ $att['url'] }}" class="h-10 w-10 rounded object-cover border {{ $msg['is_mine'] ? 'border-gpt-400/30' : 'border-slate-300' }}" alt="{{ $att['name'] }}">
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
                                        {{-- Replies --}}
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
                                {{-- Hover actions --}}
                                <div class="ml-8 mt-0.5 flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                    <button wire:click="$set('newMessage', ''); $wire.replyTo({{ $msg['id'] }})" class="text-[10px] text-slate-400 hover:text-gpt-600">
                                        Responder
                                    </button>
                                </div>
                            </div>
                            @endforeach

                            @empty($mensajes)
                            <div class="flex flex-1 flex-col items-center justify-center py-10 text-center">
                                <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="mt-3 text-sm font-medium text-slate-400">Sin mensajes aún</p>
                                <p class="text-[12px] text-slate-400">Sé el primero en enviar un mensaje.</p>
                            </div>
                            @endempty

                            {{-- Typing indicator --}}
                            @if(!empty($typingUsers))
                            <div class="flex items-center gap-1.5 px-1 text-[11px] italic text-slate-400">
                                <span class="flex gap-0.5">
                                    <span class="h-1 w-1 animate-bounce rounded-full bg-slate-400" style="animation-delay: 0ms"></span>
                                    <span class="h-1 w-1 animate-bounce rounded-full bg-slate-400" style="animation-delay: 150ms"></span>
                                    <span class="h-1 w-1 animate-bounce rounded-full bg-slate-400" style="animation-delay: 300ms"></span>
                                </span>
                                {{ implode(', ', $typingUsers) }} escribiendo...
                            </div>
                            @endif
                        </div>

                        {{-- Input --}}
                        <div class="border-t border-slate-200 px-3 py-2">
                            {{-- Attachment previews --}}
                            @if(count($attachments))
                            <div class="mb-2 flex flex-wrap gap-2">
                                @foreach($attachments as $i => $file)
                                <div class="relative group rounded-md border border-slate-200 bg-slate-50 p-1.5">
                                    @if(str_starts_with($file->getMimeType(), 'image/'))
                                    <img src="{{ $file->temporaryUrl() }}" class="h-12 w-12 rounded object-cover">
                                    @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded bg-slate-200">
                                        <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                    </div>
                                    @endif
                                    <span class="block mt-0.5 text-[9px] text-slate-500 truncate max-w-[48px]">{{ $file->getClientOriginalName() }}</span>
                                    <button wire:click="removeAttachment({{ $i }})" class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-gpt-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            <form wire:submit="sendMessage" class="flex items-end gap-2">
                                <label class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <input type="file" wire:model="attachments" multiple id="chat-file-input" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">
                                </label>
                                <div class="flex-1">
                                    <textarea
                                        wire:model.live.debounce.200ms="newMessage"
                                        id="chat-input"
                                        rows="1"
                                        placeholder="Escribe un mensaje... (@usuario para mencionar)"
                                        class="w-full resize-none rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none focus:ring-1 focus:ring-gpt-200"
                                        x-data
                                        x-on:keydown.enter.prevent="$wire.sendMessage()"
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gpt-600 text-white hover:bg-gpt-700"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex flex-1 flex-col items-center justify-center text-center">
                            <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-400">Selecciona un canal</p>
                            <p class="text-[12px] text-slate-400">Elige un canal para comenzar a chatear.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
