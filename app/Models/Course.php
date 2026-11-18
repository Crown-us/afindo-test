<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description', 'status'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['status' => CourseStatus::class];
    }

    protected static function booted(): void
    {
        // Lesson terhapus lewat FK cascade (tanpa event per lesson),
        // jadi folder video milik course dibersihkan di sini.
        static::deleted(function (Course $course) {
            Storage::disk(Lesson::VIDEO_DISK)->deleteDirectory("videos/course-{$course->id}");
        });
    }

    /** @return HasMany<Lesson, $this> */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }
}
