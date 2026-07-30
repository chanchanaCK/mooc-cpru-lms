<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Str;

class CertificateService
{
    /** Issue a certificate once (idempotent) for a completed course. */
    public function issueFor(User $user, Course $course): Certificate
    {
        return Certificate::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['certificate_number' => $this->generateNumber(), 'issued_at' => now()],
        );
    }

    private function generateNumber(): string
    {
        do {
            $number = 'MOOC-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}
