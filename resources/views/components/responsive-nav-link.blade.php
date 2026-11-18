@props(['active'])

<a {{ $attributes->merge(['class' => 'block w-full rounded-lg px-3 py-2 text-start text-base font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 '.(($active ?? false) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900')]) }}>
    {{ $slot }}
</a>
