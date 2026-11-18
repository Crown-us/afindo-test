@props(['status'])

@php
$color = match ($status) {
    \App\Enums\VideoStatus::Ready => 'green',
    \App\Enums\VideoStatus::Uploading => 'blue',
    \App\Enums\VideoStatus::Failed => 'red',
    default => 'amber',
};
@endphp

<x-badge :color="$color" {{ $attributes }}>{{ $status->label() }}</x-badge>
