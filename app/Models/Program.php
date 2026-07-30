<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'subtitle', 'description', 'thumbnail', 'type',
        'nqf_level', 'required_credits', 'duration_months', 'status', 'owner_id',
    ];

    protected function casts(): array
    {
        return ['required_credits' => 'decimal:1'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes & relationships
    |--------------------------------------------------------------------------
    */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'program_courses')
            ->withPivot(['requirement', 'sort_order', 'credits_override'])
            ->withTimestamps()
            ->orderBy('program_courses.sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ProgramEnrollment::class);
    }

    public function enrollmentFor(?User $user): ?ProgramEnrollment
    {
        return $user ? $this->enrollments()->where('user_id', $user->id)->first() : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & labels
    |--------------------------------------------------------------------------
    */
    public function getTypeLabelAttribute(): string
    {
        return QualificationType::labelFor($this->type);
    }

    public function getLevelLabelAttribute(): ?string
    {
        return QualificationLevel::labelFor($this->nqf_level);
    }

    public function getCreditsLabelAttribute(): string
    {
        return CreditRecord::fmt($this->required_credits) . ' หน่วยกิต';
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail && str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }

        if ($this->thumbnail) {
            return asset('storage/' . ltrim($this->thumbnail, '/'));
        }

        // Font-free gradient placeholder with a mortarboard glyph drawn from paths.
        $hue = abs(crc32($this->slug ?: (string) $this->id)) % 360;
        $hue2 = ($hue + 35) % 360;

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="640" height="360">
            <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="hsl({$hue},55%,48%)"/>
                <stop offset="1" stop-color="hsl({$hue2},52%,36%)"/>
            </linearGradient></defs>
            <rect width="640" height="360" fill="url(#g)"/>
            <path d="M320 132 l120 46 -120 46 -120 -46 z" fill="rgba(255,255,255,0.92)"/>
            <path d="M370 196 v40 a50 26 0 0 1 -100 0 v-40 l50 19 z" fill="rgba(255,255,255,0.75)"/>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /** Build a unique slug from a title (Thai titles fall back to a random suffix). */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($title) ?: 'program-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
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
