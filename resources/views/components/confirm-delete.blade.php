{{--
    Tombol hapus + modal konfirmasi.
    Pemakaian: <x-confirm-delete :action="route(...)" name="hapus-course-1" title="..." message="..." />
--}}
@props(['action', 'name', 'title' => 'Hapus data?', 'message' => 'Tindakan ini tidak dapat dibatalkan.', 'label' => 'Hapus'])

<button type="button" x-data @click="$dispatch('open-modal', '{{ $name }}')"
        {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2.5 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500']) }}>
    {{ $label }}
</button>

<x-modal :name="$name" maxWidth="md" focusable>
    <form method="POST" action="{{ $action }}" class="p-6 text-left">
        @csrf
        @method('DELETE')
        <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
        <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
            <x-danger-button>Ya, hapus</x-danger-button>
        </div>
    </form>
</x-modal>
