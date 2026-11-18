<x-app-layout title="Edit Lesson">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 space-y-8">
        <x-page-header :title="$lesson->title" subtitle="Lesson #{{ $lesson->position }} di course ini.">
            <x-slot name="breadcrumb">
                <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">Kelola Course</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.courses.show', $course) }}" class="hover:text-gray-700 truncate max-w-[16rem]">{{ $course->title }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-700">Edit lesson</span>
            </x-slot>
        </x-page-header>

        {{-- Video --}}
        <section aria-labelledby="video-heading" class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h2 id="video-heading" class="text-lg font-semibold text-gray-900">Video</h2>
                <x-badge :color="$lesson->video_status->color()">{{ $lesson->video_status->label() }}</x-badge>
            </div>
            @php($maxSizeKb = \App\Http\Controllers\LessonVideoController::maxSizeKb())

            <x-card class="space-y-5 p-6">
                @if ($lesson->video_status === \App\Enums\VideoStatus::Ready)
                    <video src="{{ route('lessons.video', $lesson) }}" controls preload="metadata" class="aspect-video w-full rounded-lg bg-black"></video>
                    <p class="text-sm text-gray-500">{{ $lesson->video_original_name }} ({{ Number::fileSize($lesson->video_size, 1) }})</p>
                @elseif ($lesson->video_status === \App\Enums\VideoStatus::Failed)
                    <p class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">Upload sebelumnya gagal: {{ $lesson->video_error }}</p>
                @endif

                <form x-data="videoUploader(@js(route('admin.lessons.video.store', $lesson)), {{ $maxSizeKb }})" @submit.prevent="upload" class="space-y-4">
                    <div>
                        <x-input-label for="video" :value="$lesson->video_path ? 'Ganti video' : 'Pilih file video'" />
                        <input id="video" type="file" accept="video/mp4,video/webm,video/ogg" @change="pick" :disabled="uploading"
                               class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                        <p class="mt-1 text-xs text-gray-500">MP4, WebM, atau OGG. Maksimal {{ Number::fileSize($maxSizeKb * 1024) }}.</p>
                    </div>

                    <div x-show="uploading" x-cloak class="space-y-1">
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full bg-brand-600 transition-all" :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="text-sm text-gray-600" x-text="progress < 100 ? `Mengunggah… ${progress}%` : 'Menyimpan video…'"></p>
                    </div>

                    <p x-show="error" x-cloak x-text="error" role="alert" class="text-sm text-red-600"></p>

                    <x-primary-button x-bind:disabled="!file || uploading">Unggah video</x-primary-button>
                </form>
            </x-card>
        </section>

        {{-- Data lesson --}}
        <section aria-labelledby="detail-heading" class="space-y-3">
            <h2 id="detail-heading" class="text-lg font-semibold text-gray-900">Detail lesson</h2>
            @include('admin.lessons.partials.form', [
                'action' => route('admin.lessons.update', $lesson),
                'method' => 'PUT',
                'submitLabel' => 'Simpan perubahan',
                'cancelUrl' => route('admin.courses.show', $course),
            ])
        </section>
    </div>
</x-app-layout>
