{{-- Form course, dipakai halaman tambah & edit. Variabel: $course, $action, $method, $submitLabel --}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @method($method)

    <x-card class="space-y-6 p-6">
        <div>
            <x-input-label for="title" value="Judul course" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $course->title)" required maxlength="150" autofocus placeholder="Contoh: Dasar Pemrograman Web dengan Laravel" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />
            <x-textarea id="description" name="description" rows="5" class="mt-1 block w-full" required maxlength="5000" placeholder="Jelaskan apa yang akan dipelajari student di course ini.">{{ old('description', $course->description) }}</x-textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <fieldset>
            <legend class="block text-sm font-medium text-gray-700">Status publikasi</legend>
            @php($currentStatus = old('status', $course->status?->value ?? \App\Enums\CourseStatus::Draft->value))
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    [\App\Enums\CourseStatus::Draft, 'Hanya terlihat oleh admin. Cocok saat materi masih disiapkan.'],
                    [\App\Enums\CourseStatus::Published, 'Tampil di katalog dan bisa diakses semua student.'],
                ] as [$status, $hint])
                    <label class="relative flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-4 hover:bg-gray-50 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500">
                        <input type="radio" name="status" value="{{ $status->value }}" class="mt-0.5 border-gray-300 text-brand-600 focus:ring-brand-500" @checked($currentStatus === $status->value)>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">{{ $status->label() }}</span>
                            <span class="block text-sm text-gray-500">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </fieldset>
    </x-card>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <x-link-button variant="secondary" :href="url()->previous() === url()->current() ? route('admin.courses.index') : url()->previous()">Batal</x-link-button>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
