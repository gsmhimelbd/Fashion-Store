<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'FASHION10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_spend' => 1000.00,
                'max_discount' => 500.00,
                'usage_limit' => 1000,
                'times_used' => 24,
                'is_active' => true,
                'expires_at' => now()->addYear(),
            ],
            [
                'code' => 'SAVE200',
                'type' => 'fixed',
                'value' => 200.00,
                'min_spend' => 2000.00,
                'max_discount' => 200.00,
                'usage_limit' => 500,
                'times_used' => 12,
                'is_active' => true,
                'expires_at' => now()->addYear(),
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
