<?php

namespace App\Models;

use App\Enums\VideoStatus;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'description'])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    /** Disk tempat file video disimpan (private, tidak bisa diakses langsung lewat URL). */
    public const VIDEO_DISK = 'local';

    protected $attributes = [
        'video_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'video_size' => 'integer',
            'video_status' => VideoStatus::class,
        ];
    }

    protected static function booted(): void
    {
        // Lesson baru otomatis ditaruh di urutan paling akhir course-nya.
        static::creating(function (Lesson $lesson) {
            if ($lesson->position === null) {
                $lesson->position = (int) static::where('course_id', $lesson->course_id)->max('position') + 1;
            }
        });
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function hasPlayableVideo(): bool
    {
        return $this->video_status->isPlayable() && filled($this->video_path);
    }
}
