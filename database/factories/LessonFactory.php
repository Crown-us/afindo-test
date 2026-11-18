<?php

namespace Database\Factories;

use App\Enums\VideoStatus;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(12),
            'video_status' => VideoStatus::Pending,
        ];
    }

    /** Lesson dengan video siap putar (file harus dibuat sendiri di test, mis. Storage::fake). */
    public function withVideo(string $path = 'videos/sample.mp4'): static
    {
        return $this->state(fn () => [
            'video_path' => $path,
            'video_original_name' => basename($path),
            'video_mime' => 'video/mp4',
            'video_size' => 1024,
            'video_status' => VideoStatus::Ready,
        ]);
    }
}
