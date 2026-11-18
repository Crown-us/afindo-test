{{-- Judul halaman + breadcrumb opsional + slot aksi di kanan. --}}
@props(['title', 'subtitle' => null])

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @isset($breadcrumb)
            <nav class="mb-1 flex flex-wrap items-center gap-1 text-sm text-gray-500" aria-label="Breadcrumb">{{ $breadcrumb }}</nav>
        @endisset
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 break-words">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
