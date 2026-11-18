<x-app-layout :title="$lesson->title">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('courses.index') }}" class="hover:text-gray-900">Katalog</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('courses.show', $course) }}" class="hover:text-gray-900">{{ $course->title }}</a>
        </nav>

        <div class="mt-4 grid gap-6 lg:grid-cols-3">
            {{-- Player --}}
            <div class="lg:col-span-2">
                @if ($lesson->video_status === \App\Enums\VideoStatus::Ready)
                    <video src="{{ route('lessons.video', $lesson) }}" controls controlsList="nodownload" preload="metadata"
                           class="aspect-video w-full rounded-2xl bg-black"></video>
                @else
                    <div class="flex aspect-video w-full flex-col items-center justify-center rounded-2xl bg-gray-900 px-6 text-center">
                        <p class="font-semibold text-white">Video belum tersedia</p>
                        <p class="mt-1 text-sm text-gray-400">Materi video lesson ini sedang disiapkan.</p>
                    </div>
                @endif

                <div class="mt-6">
                    <p class="text-sm font-medium text-brand-600">Lesson {{ $number }} dari {{ $lessons->count() }}</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{ $lesson->title }}</h1>
                    @if ($lesson->description)
                        <p class="mt-3 text-gray-600">{{ $lesson->description }}</p>
                    @endif
                </div>

                <div class="mt-8 flex justify-between gap-3 border-t border-gray-200 pt-6">
                    @if ($previous)
                        <x-link-button variant="secondary" :href="route('courses.lessons.show', [$course, $previous])">Lesson sebelumnya</x-link-button>
                    @else
                        <span></span>
                    @endif
                    @if ($next)
                        <x-link-button :href="route('courses.lessons.show', [$course, $next])">Lesson berikutnya</x-link-button>
                    @endif
                </div>
            </div>

            {{-- Daftar lesson --}}
            <aside aria-labelledby="lesson-list-heading">
                <x-card class="overflow-hidden lg:sticky lg:top-24">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 id="lesson-list-heading" class="font-semibold text-gray-900">Materi course</h2>
                        <p class="text-sm text-gray-500">{{ $lessons->count() }} video lesson</p>
                    </div>
                    <ol class="max-h-[60vh] space-y-1 overflow-y-auto p-2">
                        @foreach ($lessons as $item)
                            @php($active = $item->is($lesson))
                            <li>
                                <a href="{{ route('courses.lessons.show', [$course, $item]) }}" @if ($active) aria-current="page" @endif
                                   @class([
                                       'flex items-start gap-3 rounded-lg px-3 py-2.5 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500',
                                       'bg-brand-50 font-medium text-brand-700' => $active,
                                       'text-gray-700 hover:bg-gray-50' => ! $active,
                                   ])>
                                    <span @class([
                                        'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold',
                                        'bg-brand-600 text-white' => $active,
                                        'bg-gray-100 text-gray-500' => ! $active,
                                    ])>{{ $loop->iteration }}</span>
                                    <span>{{ $item->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </x-card>
            </aside>
        </div>
    </div>
</x-app-layout>
