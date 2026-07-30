<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditRecord extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'source', 'title', 'code', 'credits',
        'grade', 'score_percent', 'status', 'earned_at', 'expires_at',
        'certificate_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'decimal:1',
            'earned_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes & helpers
    |--------------------------------------------------------------------------
    */
    public function scopeEarned(Builder $query): Builder
    {
        return $query->where('status', 'earned');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'course' => 'เรียนจบคอร์ส',
            'transfer' => 'เทียบโอน',
            'manual' => 'บันทึกโดยเจ้าหน้าที่',
            default => $this->source,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'earned' => 'ได้รับแล้ว',
            'pending' => 'รออนุมัติ',
            'revoked' => 'ยกเลิก',
            'expired' => 'หมดอายุ',
            default => $this->status,
        };
    }

    /** Trim trailing zeros from a credit value: 3.0 → "3", 1.5 → "1.5". */
    public static function fmt(float|string|null $credits): string
    {
        return rtrim(rtrim(number_format((float) $credits, 1), '0'), '.');
    }
}
