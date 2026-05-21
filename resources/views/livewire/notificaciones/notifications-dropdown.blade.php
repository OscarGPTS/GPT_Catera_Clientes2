<div class="relative" x-data="{ open: false }" @click.outside="open = false" wire:poll.10s="loadNotifications">
    <button
        @click="open = !open"
        class="relative rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-600"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($noLeidas > 0)
        <span class="absolute right-1.5 top-1.5 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-gpt-600 px-1 text-[10px] font-semibold text-white">
            {{ $noLeidas > 99 ? '99+' : $noLeidas }}
        </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition.opacity
         class="absolute right-0 z-50 mt-2 w-[380px] rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-slate-800">Notificaciones</h3>
                @if($noLeidas > 0)
                <span class="rounded-full bg-gpt-100 px-2 py-0.5 text-[11px] font-medium text-gpt-700">{{ $noLeidas }} {{ $noLeidas === 1 ? 'nueva' : 'nuevas' }}</span>
                @endif
            </div>
            @if($noLeidas > 0)
            <button wire:click="markAllAsRead" class="text-[11px] font-medium text-gpt-600 hover:text-gpt-700">
                Marcar todas leídas
            </button>
            @endif
        </div>

        <div class="max-h-[400px] overflow-y-auto">
            @forelse($notificaciones as $notif)
            <a
                href="{{ $notif['link'] }}"
                wire:click="markAsRead('{{ $notif['id'] }}')"
                class="flex gap-3 border-b border-slate-50 px-4 py-3 transition-colors hover:bg-slate-50 {{ $notif['read'] ? 'opacity-60' : '' }}"
            >
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $notif['read'] ? 'bg-slate-100' : 'bg-gpt-50' }}">
                    <x-dynamic-component :component="'svg-icon.' . $notif['icon']" class="h-4 w-4 {{ $notif['read'] ? 'text-slate-400' : 'text-gpt-500' }}" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <p class="truncate text-[13px] font-medium {{ $notif['read'] ? 'text-slate-500' : 'text-slate-800' }}">{{ $notif['title'] }}</p>
                        @if(!$notif['read'])
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-gpt-500"></span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-[12px] text-slate-500">{{ $notif['message'] }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $notif['created_at'] }}</p>
                </div>
            </a>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-400">Estás al día</p>
                <p class="text-[12px] text-slate-400">No tienes notificaciones pendientes.</p>
            </div>
            @endforelse
        </div>

        <a href="/notificaciones" class="block border-t border-slate-100 px-4 py-3 text-center text-[13px] font-medium text-gpt-600 hover:bg-slate-50 hover:text-gpt-700">
            Ver todas las notificaciones
        </a>
    </div>
</div>
