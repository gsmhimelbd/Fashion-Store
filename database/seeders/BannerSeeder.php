<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Elevate Your Everyday Style',
                'subtitle' => 'Curated premium fashion accessories designed to make an impression.',
                'badge_text' => '✨ NEW COLLECTION 2026',
                'button_text' => 'Explore Shop',
                'button_url' => '/shop',
                'image_path' => 'uploads/hero-banner-1.svg',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Exclusive Luxury Watches & Bags',
                'subtitle' => 'Up to 40% OFF with Cash on Delivery nationwide across Bangladesh.',
                'badge_text' => '🔥 LIMITED TIME OFFER',
                'button_text' => 'View Deals',
                'button_url' => '/shop?category=watches-tech',
                'image_path' => 'uploads/hero-banner-2.svg',
                'is_active' => true,
                'order' => 2,
            ],
        ];

        foreach ($banners as $b) {
            Banner::updateOrCreate(['title' => $b['title']], $b);
        }
    }
}
