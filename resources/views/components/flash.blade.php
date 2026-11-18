{{-- Notifikasi singkat setelah aksi (session flash 'success' / 'error'). --}}
@foreach (['success' => 'bg-emerald-600', 'error' => 'bg-red-600'] as $key => $bg)
    @if (session($key))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity
             role="status"
             class="fixed bottom-4 right-4 left-4 sm:left-auto z-50 flex items-start gap-3 rounded-lg {{ $bg }} px-4 py-3 text-sm font-medium text-white shadow-lg sm:max-w-sm">
            <span class="flex-1">{{ session($key) }}</span>
            <button type="button" @click="show = false" class="opacity-80 hover:opacity-100" aria-label="Tutup">&times;</button>
        </div>
    @endif
@endforeach
