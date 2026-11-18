{{-- Thumbnail course; jika belum ada, tampilkan latar biru dengan inisial judul. --}}
@props(['course'])

@if ($url = $course->thumbnailUrl())
    <img src="{{ $url }}" alt="" loading="lazy" {{ $attributes->merge(['class' => 'object-cover']) }}>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-3xl font-semibold text-white/90']) }} aria-hidden="true">
        {{ Str::upper(Str::substr($course->title, 0, 1)) }}
    </div>
@endif
