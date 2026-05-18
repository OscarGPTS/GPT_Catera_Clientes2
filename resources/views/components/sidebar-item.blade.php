@props(['href' => '#', 'icon' => 'square-2-stack', 'label' => '', 'siblingHrefs' => [], 'badge' => null])

@php
$cleanHref = trim($href, '/');
$isRoot = $cleanHref === '';
$exactMatch = $isRoot ? (request()->path() === '/' || request()->path() === '') : request()->is($cleanHref);
$wildcardMatch = !$isRoot && request()->is($cleanHref . '/*');

// If another sibling item matches the current URL exactly,
// skip wildcard matching to avoid parent/prefix items staying highlighted.
$siblingExact = false;
foreach ($siblingHrefs as $siblingHref) {
    $siblingClean = trim($siblingHref, '/');
    if ($siblingClean !== $cleanHref && request()->is($siblingClean)) {
        $siblingExact = true;
        break;
    }
}

$isActive = $exactMatch || ($wildcardMatch && !$siblingExact);
@endphp

<a
    href="{{ $href }}"
    class="group relative flex items-center gap-3 rounded-lg text-sm font-medium transition-all duration-200 ease-out select-none
        {{ $isActive 
            ? 'bg-gpt-600/20 text-white ring-1 ring-inset ring-gpt-600/30 shadow-sm shadow-black/20' 
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
    <span class="relative shrink-0 transition-colors duration-200 ml-2 {{ $isActive ? 'text-gpt-400' : 'text-slate-500 group-hover:text-slate-300' }}">
        <x-dynamic-component :component="'svg-icon.' . $icon" class="h-[18px] w-[18px]" />
    </span>

    {{-- Label --}}
    <span class="truncate leading-tight p-2" :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen, 'inline': sidebarOpen || sidebarMobileOpen }">
        {{ $label }}
    </span>

    {{-- Badge --}}
    @if($badge && $badge > 0)
    <span class="absolute top-0.5 right-1.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-gpt-600 px-1 text-[9px] font-semibold text-white" :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen, 'flex': sidebarOpen || sidebarMobileOpen }">{{ $badge > 99 ? '99+' : $badge }}</span>
    <span class="absolute top-1 right-1 flex h-2 w-2 rounded-full bg-gpt-500" :class="{ 'hidden': sidebarOpen || sidebarMobileOpen, 'flex': !sidebarOpen && !sidebarMobileOpen }"></span>
    @endif

    {{-- Active dot for collapsed state --}}
    @if($isActive)
        <span class="absolute top-1 right-1 flex h-1.5 w-1.5 rounded-full bg-gpt-500 shadow-[0_0_4px_rgba(234,88,12,0.6)]" :class="{ 'hidden': sidebarOpen || sidebarMobileOpen, 'block': !sidebarOpen && !sidebarMobileOpen }"></span>
    @endif

    {{-- Ripple effect on click --}}
    <span class="absolute inset-0 rounded-lg bg-white/0 transition-colors duration-150 group-active:bg-white/10"></span>
</a>
