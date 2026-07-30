<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QualificationLevel extends Model
{
    protected $fillable = ['level', 'name', 'description', 'sort_order'];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('level');
    }

    /** Display name for a program's nqf_level (cached per request; null-safe). */
    public static function labelFor(?int $level): ?string
    {
        if ($level === null) {
            return null;
        }

        static $map = null;
        $map ??= static::pluck('name', 'level')->all();

        return $map[$level] ?? ('ระดับ ' . $level);
    }
}
