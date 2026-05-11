<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-medium text-slate-800">Notificaciones</h1>
            <p class="text-sm text-slate-500">Historial completo de notificaciones del sistema.</p>
        </div>
        <button
            wire:click="markAllAsRead"
            class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
        >
            Marcar todas como leídas
        </button>
    </div>

    <div class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white p-3">
        <div class="flex items-center gap-2">
            <button wire:click="setFilter('all')" class="rounded-md px-3 py-1.5 text-[13px] font-medium {{ $filter === 'all' ? 'bg-gpt-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Todas
            </button>
            <button wire:click="setFilter('unread')" class="rounded-md px-3 py-1.5 text-[13px] font-medium {{ $filter === 'unread' ? 'bg-gpt-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                Sin leer
            </button>
            <button wire:click="setFilter('menciones')" class="rounded-md px-3 py-1.5 text-[13px] font-medium {{ $filter === 'menciones' ? 'bg-gpt-600 text-white' : 'text-slate-500 hover:bg-slate-100' }}">
                @Menciones
            </button>
        </div>
        <div class="ml-auto">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar notificaciones..." class="w-64 rounded-md border border-slate-200 py-1.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
            </div>
        </div>
    </div>

    <div class="space-y-2">
        @forelse($this->notifications as $notif)
        <div class="flex items-start gap-4 rounded-lg border border-slate-200 bg-white p-4 transition-colors hover:bg-slate-50 {{ $notif->read_at ? 'opacity-60' : '' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $notif->read_at ? 'bg-slate-100' : 'bg-gpt-50' }}">
                <x-dynamic-component :component="'svg-icon.' . ($notif->data['icon'] ?? 'bell')" class="h-5 w-5 {{ $notif->read_at ? 'text-slate-400' : 'text-gpt-500' }}" />
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-medium {{ $notif->read_at ? 'text-slate-500' : 'text-slate-800' }}">{{ $notif->data['title'] ?? 'Notificación' }}</p>
                        <p class="mt-0.5 text-[13px] text-slate-600">{{ $notif->data['message'] ?? '' }}</p>
                    </div>
                    @if(!$notif->read_at)
                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-gpt-500"></span>
                    @endif
                </div>
                <div class="mt-2 flex items-center gap-3">
                    <a
                        href="{{ $notif->data['link'] ?? '#' }}"
                        wire:click="markAsRead('{{ $notif->id }}')"
                        class="text-[13px] font-medium text-gpt-600 hover:text-gpt-700"
                    >
                        {{ $notif->data['action'] ?? 'Ver detalle' }}
                    </a>
                    @if(!$notif->read_at)
                    <button wire:click="markAsRead('{{ $notif->id }}')" class="text-[12px] text-slate-400 hover:text-slate-600">
                        Marcar como leída
                    </button>
                    @endif
                    <button wire:click="delete('{{ $notif->id }}')" class="ml-auto text-[12px] text-slate-400 hover:text-gpt-red-600">
                        Eliminar
                    </button>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">{{ $notif->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-white py-16">
            <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="mt-4 text-sm font-medium text-slate-400">No hay notificaciones</p>
            <p class="text-[13px] text-slate-400">Estás al día con todas las notificaciones.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $this->notifications->links() }}
    </div>
</div>
