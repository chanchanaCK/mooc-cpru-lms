<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'instructor_id', 'category_id', 'title', 'slug', 'subtitle', 'description',
        'thumbnail', 'price', 'level', 'language', 'status',
        'duration_minutes', 'students_count', 'lessons_count', 'rating_avg', 'rating_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'rating_avg' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('subtitle', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['progress_percent', 'completed_at'])
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function certificateFor(?User $user): ?Certificate
    {
        return $user ? $this->certificates()->where('user_id', $user->id)->first() : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & helpers
    |--------------------------------------------------------------------------
    */
    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function getPriceLabelAttribute(): string
    {
        return $this->isFree() ? 'ฟรี' : '฿' . number_format((float) $this->price);
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'beginner' => 'เริ่มต้น',
            'intermediate' => 'ปานกลาง',
            'advanced' => 'ขั้นสูง',
            default => $this->level,
        };
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail && str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }

        if ($this->thumbnail) {
            return asset('storage/' . ltrim($this->thumbnail, '/'));
        }

        // Self-contained gradient placeholder (no external dependency) with a
        // deterministic hue and a play-button drawn from SVG shapes (font-free).
        $hue = abs(crc32($this->slug ?: (string) $this->id)) % 360;
        $hue2 = ($hue + 40) % 360;

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="640" height="360">
            <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="hsl({$hue},62%,55%)"/>
                <stop offset="1" stop-color="hsl({$hue2},58%,42%)"/>
            </linearGradient></defs>
            <rect width="640" height="360" fill="url(#g)"/>
            <circle cx="320" cy="180" r="56" fill="rgba(255,255,255,0.92)"/>
            <path d="M303 152 l42 28 -42 28 z" fill="hsl({$hue},60%,45%)"/>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function getDurationLabelAttribute(): string
    {
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;

        return $h > 0 ? "{$h} ชม. {$m} นาที" : "{$m} นาที";
    }

    /** The lesson a learner should start with. */
    public function firstLesson(): ?Lesson
    {
        return $this->lessons()->orderBy('sort_order')->first();
    }

    /** Recompute cached content counters from the current lessons. */
    public function syncContentCounts(): void
    {
        $this->update([
            'lessons_count' => $this->lessons()->count(),
            'duration_minutes' => (int) round($this->lessons()->sum('duration_seconds') / 60),
        ]);
    }

    /** Build a unique slug from a title (Thai titles fall back to a random suffix). */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($title) ?: 'course-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
        $slug = $base;
        $i = 1;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
