<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type', // 'percentage' or 'fixed'
        'value',
        'min_spend',
        'max_discount',
        'usage_limit',
        'times_used',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isValidFor($subtotal)
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->min_spend && $subtotal < $this->min_spend) return false;
        if ($this->usage_limit && $this->times_used >= $this->usage_limit) return false;
        return true;
    }

    public function calculateDiscount($subtotal)
    {
        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100;
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
            return $discount;
        }
        return min($this->value, $subtotal);
    }
}
