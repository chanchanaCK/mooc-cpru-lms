<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = [
        'section_id', 'course_id', 'title', 'type',
        'video_provider', 'video_id', 'duration_seconds',
        'content', 'is_preview', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public const QUIZ_PASS_PERCENT = 70;

    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isQuiz(): bool
    {
        return $this->type === 'quiz';
    }

    public function latestAttemptFor(?User $user): ?QuizAttempt
    {
        if (! $user) {
            return null;
        }

        return $this->quizAttempts()->where('user_id', $user->id)->latest()->first();
    }

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->completions()->where('user_id', $user->id)->exists();
    }

    /** Embeddable player URL for the lesson's video. */
    public function getEmbedUrlAttribute(): ?string
    {
        return match ($this->video_provider) {
            'youtube' => "https://www.youtube-nocookie.com/embed/{$this->video_id}",
            'vimeo' => "https://player.vimeo.com/video/{$this->video_id}",
            'bunny' => $this->video_id, // full iframe src stored as-is
            'file' => asset('storage/' . ltrim((string) $this->video_id, '/')),
            default => null,
        };
    }

    public function getDurationLabelAttribute(): string
    {
        $m = intdiv($this->duration_seconds, 60);
        $s = $this->duration_seconds % 60;

        return sprintf('%d:%02d', $m, $s);
    }

    /** Normalise a pasted URL or raw id into the provider's video id. */
    public static function extractVideoId(?string $provider, ?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if ($provider === 'youtube') {
            if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $input, $m)) {
                return $m[1];
            }

            return preg_match('~^[\w-]{11}$~', $input) ? $input : $input;
        }

        if ($provider === 'vimeo') {
            if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $input, $m)) {
                return $m[1];
            }

            return preg_match('~^\d+$~', $input) ? $input : $input;
        }

        return $input;
    }
}
