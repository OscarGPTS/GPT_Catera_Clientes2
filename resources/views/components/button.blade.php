@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
$base = 'inline-flex items-center gap-1.5 rounded-md text-sm font-medium transition-colors';

$variants = [
    'primary' => 'bg-gpt-600 text-white hover:bg-gpt-700 px-4 py-2',
    'secondary' => 'border border-gpt-600 text-gpt-600 hover:bg-gpt-50 px-4 py-2',
    'critical' => 'bg-gpt-red-600 text-white hover:bg-gpt-red-700 px-4 py-2',
    'ghost' => 'text-slate-600 hover:bg-slate-100 px-4 py-2',
];

$class = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
$class .= ' ' . ($attributes->get('class') ?? '');
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $class }}" {{ $attributes->except(['class', 'variant', 'href']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" class="{{ $class }}" {{ $attributes->except(['class', 'variant']) }}>{{ $slot }}</button>
@endif
