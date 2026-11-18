{{-- Tautan bergaya tombol (menghindari <a><button></button></a> yang tidak valid). --}}
@props(['variant' => 'primary'])

@php
$classes = [
    'primary' => 'bg-brand-600 text-white border-transparent hover:bg-brand-700 focus:ring-brand-500',
    'secondary' => 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-brand-500',
][$variant];
@endphp

<a {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 transition $classes"]) }}>
    {{ $slot }}
</a>
