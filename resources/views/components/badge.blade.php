@props(['color' => 'gray'])

@php
$colors = [
    'gray' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
    'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
    'blue' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
    'red' => 'bg-red-50 text-red-700 ring-red-600/20',
][$color] ?? '';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset whitespace-nowrap $colors"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $slot }}
</span>
