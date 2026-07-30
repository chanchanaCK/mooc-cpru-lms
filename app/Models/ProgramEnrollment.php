<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEnrollment extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'status', 'credits_earned', 'completed_at', 'certificate_number',
    ];

    protected function casts(): array
    {
        return [
            'credits_earned' => 'decimal:1',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** Progress toward the program's required credits (0–100). */
    public function progressPercent(): int
    {
        $required = (float) ($this->program?->required_credits ?? 0);

        if ($required <= 0) {
            return $this->isCompleted() ? 100 : 0;
        }

        return min(100, (int) floor((float) $this->credits_earned / $required * 100));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'กำลังเรียน',
            'completed' => 'สำเร็จการศึกษา',
            'withdrawn' => 'ถอนแล้ว',
            default => $this->status,
        };
    }
}
