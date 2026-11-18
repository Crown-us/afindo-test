<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description', 'status'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    /** Thumbnail bukan data sensitif, jadi disimpan di disk public (perlu `php artisan storage:link`). */
    public const THUMBNAIL_DISK = 'public';

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

            if ($course->thumbnail_path) {
                Storage::disk(self::THUMBNAIL_DISK)->delete($course->thumbnail_path);
            }
        });
    }

    /** Simpan thumbnail baru lalu hapus yang lama. Belum memanggil save(). */
    public function replaceThumbnail(UploadedFile $file): void
    {
        $old = $this->thumbnail_path;
        $this->thumbnail_path = $file->store('thumbnails', self::THUMBNAIL_DISK);

        if ($old) {
            Storage::disk(self::THUMBNAIL_DISK)->delete($old);
        }
    }

    /**
     * URL lewat asset() mengikuti host yang sedang dibuka, jadi thumbnail tetap tampil
     * walau aplikasi dijalankan di port/domain yang berbeda dari APP_URL.
     */
    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? asset('storage/'.$this->thumbnail_path) : null;
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
