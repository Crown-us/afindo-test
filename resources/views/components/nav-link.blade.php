@props(['active'])

<a {{ $attributes->merge(['class' => 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 '.(($active ?? false) ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900')]) }}>
    {{ $slot }}
</a>
