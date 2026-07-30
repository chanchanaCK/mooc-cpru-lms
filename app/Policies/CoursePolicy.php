<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /** Owner (or admin) may author/manage the course and its curriculum. */
    public function manage(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->instructor_id === $user->id;
    }

    public function update(User $user, Course $course): bool
    {
        return $this->manage($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->manage($user, $course);
    }
}
