@props(['href' => '#', 'icon' => 'square-2-stack', 'label' => ''])

@php
$isActive = request()->is(trim($href, '/')) || (trim($href, '/') !== '' && request()->is(trim($href, '/') . '/*'));
@endphp

<a
    href="{{ $href }}"
    class="group relative flex items-center gap-3 rounded-lg text-sm font-medium transition-all duration-200 ease-out select-none
        {{ $isActive 
            ? 'bg-slate-800 text-white shadow-sm shadow-black/20' 
            : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}"
    :class="{
        'justify-center p-2.5': !sidebarOpen && !sidebarMobileOpen,
        'px-3 py-2.5': sidebarOpen || sidebarMobileOpen
    }"
    @if(!$isActive)
    x-data
    @endif
>

    {{-- Active indicator bar --}}
    @if($isActive)
        <div class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-gpt-500 shadow-[0_0_8px_rgba(234,88,12,0.4)]"></div>
    @else
        <div class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-gpt-500 opacity-0 shadow-[0_0_8px_rgba(234,88,12,0.4)] transition-opacity duration-200 group-hover:opacity-60 scale-y-75 group-hover:scale-y-100"></div>
    @endif

    {{-- Icon --}}
    <span class="relative shrink-0 transition-colors duration-200 {{ $isActive ? 'text-gpt-400' : 'text-slate-500 group-hover:text-slate-300' }}">
        <x-dynamic-component :component="'svg-icon.' . $icon" class="h-[18px] w-[18px]" />
    </span>

    {{-- Label --}}
    <span class="truncate leading-tight" :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen, 'inline': sidebarOpen || sidebarMobileOpen }">
        {{ $label }}
    </span>

    {{-- Active dot for collapsed state --}}
    @if($isActive)
        <span class="absolute top-1 right-1 flex h-1.5 w-1.5 rounded-full bg-gpt-500 shadow-[0_0_4px_rgba(234,88,12,0.6)]" :class="{ 'hidden': sidebarOpen || sidebarMobileOpen, 'block': !sidebarOpen && !sidebarMobileOpen }"></span>
    @endif

    {{-- Ripple effect on click --}}
    <span class="absolute inset-0 rounded-lg bg-white/0 transition-colors duration-150 group-active:bg-white/10"></span>
</a>
