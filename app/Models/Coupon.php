<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_amount', 'max_uses', 'used_count', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public static function findByCode(?string $code): ?self
    {
        if (! $code) {
            return null;
        }

        return static::whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))])->first();
    }

    /** Whether the coupon can be used at all right now. */
    public function isRedeemable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /** Whether this order subtotal meets the coupon's minimum. */
    public function appliesTo(float $subtotal): bool
    {
        return $subtotal >= (float) $this->min_amount;
    }

    /** The discount amount for a given subtotal (never exceeds it). */
    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * (float) $this->value / 100
            : (float) $this->value;

        return round(min($discount, $subtotal), 2);
    }

    public function getLabelAttribute(): string
    {
        return $this->type === 'percent'
            ? 'ลด ' . rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '%'
            : 'ลด ฿' . number_format((float) $this->value);
    }
}
