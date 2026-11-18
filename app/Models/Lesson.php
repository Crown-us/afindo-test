<?php

namespace App\Models;

use App\Enums\VideoStatus;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description'])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    /** Disk private: file tidak bisa diakses langsung lewat URL. */
    public const VIDEO_DISK = 'local';

    protected $attributes = [
        'video_status' => 'pending',
    ];

    protected function casts(): array
    {
        return ['video_status' => VideoStatus::class];
    }

    protected static function booted(): void
    {
        // Lesson baru otomatis ditaruh di urutan paling akhir.
        static::creating(function (Lesson $lesson) {
            $lesson->position ??= Lesson::where('course_id', $lesson->course_id)->max('position') + 1;
        });

        static::deleted(function (Lesson $lesson) {
            if ($lesson->video_path) {
                Storage::disk(self::VIDEO_DISK)->delete($lesson->video_path);
            }
        });
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
