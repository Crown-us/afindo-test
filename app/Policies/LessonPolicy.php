<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /** Admin boleh melihat semua lesson; student hanya lesson dari course yang sudah publish. */
    public function view(User $user, Lesson $lesson): bool
    {
        return $user->isAdmin() || $lesson->course->isPublished();
    }
}
