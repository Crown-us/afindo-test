<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'description', 'status'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            if (blank($course->slug)) {
                $course->slug = static::uniqueSlug($course->title, $course->id);
            }

            // Catat kapan course pertama kali dipublish; kosongkan saat kembali ke draft.
            if ($course->isDirty('status')) {
                $course->published_at = $course->status === CourseStatus::Published
                    ? ($course->published_at ?? now())
                    : null;
            }
        });

        // Lesson ikut terhapus lewat FK cascade (tanpa event per lesson),
        // jadi folder video milik course dibersihkan di sini.
        static::deleted(function (Course $course) {
            Storage::disk(Lesson::VIDEO_DISK)->deleteDirectory($course->videoDirectory());
        });
    }

    /** Folder penyimpanan video untuk seluruh lesson di course ini. */
    public function videoDirectory(): string
    {
        return "videos/course-{$this->id}";
    }

    /**
     * Simpan urutan lesson sesuai daftar id (indeks 0 => posisi 1).
     *
     * @param  array<int, int|string>  $lessonIds
     */
    public function reorderLessons(array $lessonIds): void
    {
        DB::transaction(function () use ($lessonIds) {
            foreach (array_values($lessonIds) as $index => $id) {
                // Query tanpa orderBy dari relasi lessons(): UPDATE ... ORDER BY tidak didukung SQLite.
                Lesson::where('course_id', $this->id)->whereKey($id)->update(['position' => $index + 1]);
            }
        });
    }

    /** Rapatkan kembali posisi (1..n) setelah ada lesson yang dihapus. */
    public function normalizeLessonPositions(): void
    {
        $this->reorderLessons($this->lessons()->pluck('id')->all());
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /** @return HasMany<Lesson, $this> */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position')->orderBy('id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @param Builder<Course> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', CourseStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }
}
