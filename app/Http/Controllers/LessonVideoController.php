<?php

namespace App\Http\Controllers;

use App\Enums\VideoStatus;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class LessonVideoController extends Controller
{
    /** Batas aplikasi: 500 MB. */
    private const MAX_SIZE_KB = 512000;

    /** Batas efektif = yang terkecil antara batas aplikasi dan batas PHP (upload_max_filesize/post_max_size). */
    public static function maxSizeKb(): int
    {
        return (int) min(self::MAX_SIZE_KB, UploadedFile::getMaxFilesize() / 1024);
    }

    /** Upload video lesson (admin). Dipanggil via XHR agar progress bisa ditampilkan. */
    public function store(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/ogg', 'max:'.self::maxSizeKb()],
        ]);

        $file = $request->file('video');
        // Kolom video sengaja tidak fillable (hanya diisi di sini), jadi pakai forceFill.
        $lesson->forceFill(['video_status' => VideoStatus::Uploading, 'video_error' => null])->save();

        try {
            $path = $file->store("videos/course-{$lesson->course_id}/lesson-{$lesson->id}", Lesson::VIDEO_DISK);
        } catch (Throwable $e) {
            Log::error('Upload video gagal', ['lesson' => $lesson->id, 'error' => $e->getMessage()]);
            $lesson->forceFill(['video_status' => VideoStatus::Failed, 'video_error' => 'File gagal disimpan di server.'])->save();

            return response()->json(['message' => 'Video gagal disimpan. Silakan coba lagi.'], 500);
        }

        // Video lama baru dihapus setelah video baru berhasil disimpan.
        if ($lesson->video_path) {
            Storage::disk(Lesson::VIDEO_DISK)->delete($lesson->video_path);
        }

        $lesson->forceFill([
            'video_path' => $path,
            'video_original_name' => $file->getClientOriginalName(),
            'video_mime' => $file->getMimeType(),
            'video_size' => $file->getSize(),
            'video_status' => VideoStatus::Ready,
        ])->save();

        return response()->json(['message' => 'Video berhasil diunggah.']);
    }

    /** Putar video (admin & student). File ada di disk private, jadi hanya bisa diakses lewat route ini. */
    public function show(Lesson $lesson): BinaryFileResponse
    {
        Gate::authorize('view', $lesson);
        abort_unless($lesson->video_status === VideoStatus::Ready, 404);

        // BinaryFileResponse mendukung HTTP Range, jadi video bisa di-seek.
        return response()->file(Storage::disk(Lesson::VIDEO_DISK)->path($lesson->video_path));
    }
}
