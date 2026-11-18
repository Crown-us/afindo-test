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
}
