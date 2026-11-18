{{-- Form data teks lesson. Variabel: $lesson, $action, $method, $submitLabel, $cancelUrl --}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @method($method)

    <x-card class="space-y-6 p-6">
        <div>
            <x-input-label for="title" value="Judul lesson" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $lesson->title)" required maxlength="150" :autofocus="! $lesson->exists" placeholder="Contoh: Routing dan Controller" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi (opsional)" />
            <x-textarea id="description" name="description" rows="4" class="mt-1 block w-full" maxlength="5000" placeholder="Ringkasan singkat isi lesson.">{{ old('description', $lesson->description) }}</x-textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>
    </x-card>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <x-link-button variant="secondary" :href="$cancelUrl">Batal</x-link-button>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
