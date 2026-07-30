<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QualificationType extends Model
{
    protected $fillable = ['slug', 'name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** Display name for a program type slug (cached per request; falls back to the slug). */
    public static function labelFor(?string $slug): string
    {
        static $map = null;
        $map ??= static::pluck('name', 'slug')->all();

        return $map[$slug] ?? ($slug ?? '—');
    }
}
