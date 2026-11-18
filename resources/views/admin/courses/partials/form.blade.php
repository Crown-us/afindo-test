{{-- Form course, dipakai halaman tambah & edit. Variabel: $course, $action, $method, $submitLabel --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
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

        {{-- Thumbnail: pratinjau langsung muncul saat file dipilih --}}
        <div x-data="{ preview: @js($course->thumbnailUrl()) }">
            <x-input-label for="thumbnail" value="Thumbnail (opsional)" />
            <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="aspect-video w-full shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100 sm:w-48">
                    <img x-show="preview" :src="preview" alt="" class="h-full w-full object-cover">
                    <div x-show="!preview" class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada gambar</div>
                </div>
                <div>
                    <input id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png,image/webp"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview"
                           class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, atau WebP. Maksimal 2 MB. Rasio 16:9 paling pas.</p>
                </div>
            </div>
            <x-input-error :messages="$errors->get('thumbnail')" class="mt-2" />
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
