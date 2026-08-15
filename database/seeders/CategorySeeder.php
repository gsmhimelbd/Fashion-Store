<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Men Collection',
                'slug' => 'men-collection',
                'icon' => 'fa-person',
                'description' => 'Premium luxury watches, leather wallets, formal belts, silk ties, and modern accessories for men.',
            ],
            [
                'name' => 'Women Collection',
                'slug' => 'women-collection',
                'icon' => 'fa-person-dress',
                'description' => 'Designer handbags, pearl necklaces, sterling silver jewelry, sunglasses, and elegant accessories for women.',
            ],
            [
                'name' => 'New Arrivals',
                'slug' => 'new-arrivals',
                'icon' => 'fa-sparkles',
                'description' => 'The latest trends and hot trending fashion accessories of the 2026 season.',
            ],
            [
                'name' => 'Watches & Tech',
                'slug' => 'watches-tech',
                'icon' => 'fa-clock',
                'description' => 'Luxury chronograph timepieces and AMOLED smartwatches with premium build.',
            ],
            [
                'name' => 'Leather Goods',
                'slug' => 'leather-goods',
                'icon' => 'fa-wallet',
                'description' => 'Handcrafted full-grain leather wallets, travel backpacks, card holders, and belts.',
            ],
            [
                'name' => 'Jewelry & Fragrance',
                'slug' => 'jewelry-fragrance',
                'icon' => 'fa-gem',
                'description' => 'Long-lasting luxury Eau De Parfum and sterling silver crystal rings and pendants.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
