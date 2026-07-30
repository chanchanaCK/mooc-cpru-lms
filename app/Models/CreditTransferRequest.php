<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransferRequest extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'source_type', 'source_name', 'course_name',
        'credits_requested', 'evidence_note', 'evidence_path', 'status',
        'credits_awarded', 'grade', 'reviewed_by', 'reviewed_at', 'review_note',
        'credit_record_id',
    ];

    protected function casts(): array
    {
        return [
            'credits_requested' => 'decimal:1',
            'credits_awarded' => 'decimal:1',
            'reviewed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships & scopes
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creditRecord(): BelongsTo
    {
        return $this->belongsTo(CreditRecord::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | Labels
    |--------------------------------------------------------------------------
    */
    public function getSourceTypeLabelAttribute(): string
    {
        return match ($this->source_type) {
            'institution' => 'สถาบันการศึกษา',
            'experience' => 'ประสบการณ์ทำงาน',
            'other' => 'อื่น ๆ',
            default => $this->source_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'รออนุมัติ',
            'approved' => 'อนุมัติแล้ว',
            'rejected' => 'ไม่อนุมัติ',
            default => $this->status,
        };
    }
}
