<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $men = Category::where('slug', 'men-collection')->first();
        $women = Category::where('slug', 'women-collection')->first();
        $new = Category::where('slug', 'new-arrivals')->first();
        $watches = Category::where('slug', 'watches-tech')->first();
        $leather = Category::where('slug', 'leather-goods')->first();
        $jewelry = Category::where('slug', 'jewelry-fragrance')->first();

        $products = [
            [
                'category_id' => $watches->id ?? 1,
                'name' => 'Luxury Chronograph Sapphire Watch',
                'slug' => 'luxury-chronograph-sapphire-watch',
                'sku' => 'WAT-001',
                'short_description' => 'Precision Japanese quartz movement with scratch-resistant sapphire crystal glass and genuine stainless steel strap.',
                'description' => "Elevate your sophistication with the Luxury Chronograph Watch. Crafted from premium 316L surgical-grade stainless steel with an anti-reflective sapphire crystal glass face. Features 50M water resistance, luminous hands for night visibility, and an integrated stopwatch function.\n\n• Dial Diameter: 42mm\n• Band Width: 22mm\n• Water Resistance: 5 ATM / 50M\n• Movement: Multi-function Quartz Chronograph\n• Warranty: 2 Years Official Warranty",
                'price' => 3850.00,
                'sale_price' => 3250.00,
                'stock' => 25,
                'is_featured' => true,
                'rating' => 4.9,
                'reviews_count' => 38,
                'image' => 'uploads/luxury-watch.svg',
            ],
            [
                'category_id' => $leather->id ?? 2,
                'name' => 'Handcrafted Full-Grain Leather Wallet',
                'slug' => 'handcrafted-full-grain-leather-wallet',
                'sku' => 'WAL-002',
                'short_description' => '100% genuine vintage brown cowhide leather with RFID blocking technology and multiple card slots.',
                'description' => "Experience the unmatched luxury of genuine full-grain cowhide leather. Each wallet develops a unique, rich patina over time. Built with military-grade RFID protection to keep your credit cards safe from unauthorized wireless scans.\n\n• 8 Card Slots + 2 Cash Compartments + 1 ID Window\n• Slim bifold profile: 11.5cm x 9.5cm\n• Lifetime stitch warranty",
                'price' => 1450.00,
                'sale_price' => 1190.00,
                'stock' => 40,
                'is_featured' => true,
                'rating' => 4.8,
                'reviews_count' => 64,
                'image' => 'uploads/leather-wallet.svg',
            ],
            [
                'category_id' => $men->id ?? 1,
                'name' => 'Aviator Polarized Titanium Sunglasses',
                'slug' => 'aviator-polarized-titanium-sunglasses',
                'sku' => 'SUN-003',
                'short_description' => 'Ultra-lightweight titanium alloy frame with HD polarized UV400 anti-glare lenses.',
                'description' => "Classic aviator styling re-engineered with modern aerospace titanium materials. Glare-free polarized lenses block 100% of UVA and UVB radiation while enhancing optical clarity and vibrant color perception.\n\n• Frame Material: Beta-Titanium\n• Lens Material: Multi-layer Polarized TAC\n• Includes hard protective travel case and microfiber cleaning cloth",
                'price' => 1850.00,
                'sale_price' => 1490.00,
                'stock' => 30,
                'is_featured' => true,
                'rating' => 4.7,
                'reviews_count' => 42,
                'image' => 'uploads/polaroid-sunglasses.svg',
            ],
            [
                'category_id' => $women->id ?? 2,
                'name' => 'Minimalist Luxury Leather Handbag',
                'slug' => 'minimalist-luxury-leather-handbag',
                'sku' => 'BAG-004',
                'short_description' => 'Italian style premium calfskin leather handbag with detachable shoulder strap and gold-tone hardware.',
                'description' => "The epitome of modern elegance. This structured tote handbag is crafted from soft Italian calfskin leather. Featuring dual top handles, an adjustable crossbody strap, and smooth magnetic clasp closure.\n\n• Dimensions: 28cm x 20cm x 12cm\n• Dual compartment with zipped security pocket\n• Gold-plated rust-proof metal feet and zipper pulls",
                'price' => 4200.00,
                'sale_price' => 3650.00,
                'stock' => 15,
                'is_featured' => true,
                'rating' => 5.0,
                'reviews_count' => 29,
                'image' => 'uploads/designer-handbag.svg',
            ],
            [
                'category_id' => $leather->id ?? 2,
                'name' => 'Classic Reversible Leather Formal Belt',
                'slug' => 'classic-reversible-leather-formal-belt',
                'sku' => 'BLT-005',
                'short_description' => '2-in-1 Black & Brown reversible leather belt with rotatable brushed nickel alloy buckle.',
                'description' => "One belt for all occasions. Twist the buckle effortlessly to switch between classic Black and rich Dark Brown. Made from durable split cowhide leather that never cracks or stretches out of shape.\n\n• Width: 3.5cm (Fits all standard suit trousers and jeans)\n• Reversible rotating buckle\n• Easily customizable length",
                'price' => 1100.00,
                'sale_price' => 890.00,
                'stock' => 50,
                'is_featured' => false,
                'rating' => 4.6,
                'reviews_count' => 51,
                'image' => 'uploads/leather-belt.svg',
            ],
            [
                'category_id' => $watches->id ?? 1,
                'name' => 'Pro Ultra AMOLED Smartwatch',
                'slug' => 'pro-ultra-amoled-smartwatch',
                'sku' => 'SMW-006',
                'short_description' => '1.96-inch HD AMOLED always-on display, Bluetooth calling, heart rate & SpO2 health tracking.',
                'description' => "Stay connected with style. Features an ultra-bright AMOLED display visible in bright sunlight, crystal-clear Bluetooth phone call capability, over 100 sports modes, and up to 10 days of battery life on a single magnetic charge.\n\n• 1.96\" AMOLED display (410x502 resolution)\n• IP68 Waterproof\n• Compatible with Android & iOS",
                'price' => 3200.00,
                'sale_price' => 2750.00,
                'stock' => 20,
                'is_featured' => true,
                'rating' => 4.9,
                'reviews_count' => 77,
                'image' => 'uploads/smart-watch.svg',
            ],
            [
                'category_id' => $jewelry->id ?? 3,
                'name' => 'Freshwater Pearl Pendant Necklace',
                'slug' => 'freshwater-pearl-pendant-necklace',
                'sku' => 'JWL-007',
                'short_description' => 'Cultured natural freshwater pearl on an 18K yellow gold plated hypoallergenic chain.',
                'description' => "Timeless grace and delicate craftsmanship. A genuine lustrous freshwater baroque pearl suspended from an 18K gold-vermeil curb chain. Hypoallergenic, nickel-free, and lead-free.\n\n• Pearl Size: 8-9mm grade AAA\n• Chain Length: 45cm + 5cm extender\n• Comes in a luxury velvet presentation jewelry box",
                'price' => 1650.00,
                'sale_price' => 1350.00,
                'stock' => 18,
                'is_featured' => true,
                'rating' => 4.9,
                'reviews_count' => 35,
                'image' => 'uploads/pearl-necklace.svg',
            ],
            [
                'category_id' => $jewelry->id ?? 3,
                'name' => 'Eau De Parfum Noir Edition (100ml)',
                'slug' => 'eau-de-parfum-noir-edition',
                'sku' => 'PER-008',
                'short_description' => 'Sensual blend of rich bergamot, smoky amber, oud wood, and warm Madagascar vanilla.',
                'description' => "An irresistible masculine-leaning luxury unisex fragrance. Boasts a 25% oil concentration for an enduring 12+ hours projection and sillage that leaves an unforgettable signature aura.\n\n• Top Notes: Calabrian Bergamot, Pink Pepper\n• Heart Notes: Smoked Oud, Turkish Rose, Patchouli\n• Base Notes: Ambergris, Madagascar Vanilla, Vetiver",
                'price' => 2800.00,
                'sale_price' => 2290.00,
                'stock' => 22,
                'is_featured' => true,
                'rating' => 4.9,
                'reviews_count' => 93,
                'image' => 'uploads/perfume-bottle.svg',
            ],
            [
                'category_id' => $leather->id ?? 2,
                'name' => 'Urban Water-Resistant Leather Backpack',
                'slug' => 'urban-water-resistant-leather-backpack',
                'sku' => 'BAG-009',
                'short_description' => 'Sleek commuter leather backpack with 15.6-inch padded laptop sleeve and USB charging port.',
                'description' => "Engineered for modern professionals and travelers. Crafted from weather-proof matte synthetic leather with breathable honeycomb mesh shoulder straps for all-day carrying comfort.\n\n• Dedicated padded compartment for 15.6\" laptops & 11\" tablets\n• Luggage pass-through strap for travel suitcases\n• Hidden anti-theft back zipper pocket",
                'price' => 2950.00,
                'sale_price' => 2450.00,
                'stock' => 12,
                'is_featured' => false,
                'rating' => 4.7,
                'reviews_count' => 24,
                'image' => 'uploads/leather-backpack.svg',
            ],
            [
                'category_id' => $men->id ?? 1,
                'name' => '100% Jacquard Silk Tie & Cufflinks Set',
                'slug' => 'jacquard-silk-tie-cufflinks-set',
                'sku' => 'TIE-010',
                'short_description' => 'Handwoven pure mulberry silk necktie, pocket square, matching cufflinks, and silver tie clip.',
                'description' => "Complete executive gift set. Includes a luxury 8cm wide jacquard woven necktie, matching handkerchief, stainless steel cufflinks, and tie bar in a magnetic hard gift box.\n\n• Material: 100% 1200-stitch Jacquard Mulberry Silk\n• Tie Width: 8.5cm, Length: 150cm\n• Perfect for weddings, corporate meetings, and formal galas",
                'price' => 1400.00,
                'sale_price' => 1050.00,
                'stock' => 35,
                'is_featured' => false,
                'rating' => 4.8,
                'reviews_count' => 19,
                'image' => 'uploads/silk-tie-set.svg',
            ],
            [
                'category_id' => $jewelry->id ?? 3,
                'name' => '925 Sterling Silver Crystal Ring',
                'slug' => '925-sterling-silver-crystal-ring',
                'sku' => 'RNG-011',
                'short_description' => 'Adjustable open-band cocktail ring with sparkling Austrian cubic zirconia stones.',
                'description' => "Stunning brilliance and radiant sparkle. Hand-set with flawless Austrian 5A cubic zirconia crystals in pure solid 925 sterling silver with rhodium anti-tarnish plating.\n\n• Size: Adjustable comfortable open band (Fits US 6 to 9)\n• Rhodium finish resists scratching and oxidization",
                'price' => 1250.00,
                'sale_price' => 950.00,
                'stock' => 28,
                'is_featured' => false,
                'rating' => 4.8,
                'reviews_count' => 31,
                'image' => 'uploads/diamond-ring.svg',
            ],
            [
                'category_id' => $new->id ?? 3,
                'name' => 'Vintage Embroidered Cotton Baseball Cap',
                'slug' => 'vintage-embroidered-cotton-baseball-cap',
                'sku' => 'CAP-012',
                'short_description' => 'Washed vintage distressed cotton twill cap with 3D embroidery and antique brass buckle closure.',
                'description' => "Effortless casual streetwear style. Features washed breathable organic cotton fabric, a pre-curved visor, and an adjustable metal backstrap for an optimal fit on any head size.\n\n• Material: 100% Washed Chino Cotton\n• One Size Fits All (56-60cm circumference)",
                'price' => 750.00,
                'sale_price' => 590.00,
                'stock' => 60,
                'is_featured' => true,
                'rating' => 4.6,
                'reviews_count' => 48,
                'image' => 'uploads/cotton-cap.svg',
            ],
        ];

        foreach ($products as $pData) {
            $imagePath = $pData['image'];
            unset($pData['image']);

            $product = Product::updateOrCreate(['slug' => $pData['slug']], $pData);

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'image_path' => $imagePath],
                ['is_primary' => true]
            );
        }
    }
}
