@props(['status'])

<x-badge :color="$status === \App\Enums\CourseStatus::Published ? 'green' : 'gray'" {{ $attributes }}>
    {{ $status->label() }}
</x-badge>
