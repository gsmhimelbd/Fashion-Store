<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'store_name' => 'OnlineBdMart',
            'store_tagline' => 'Premium Fashion & Accessories',
            'store_logo' => 'images/logo.svg',
            'contact_phone' => '01775153740',
            'contact_email' => 'support@onlinebdmart.com',
            'whatsapp_number' => '01775153740',
            'delivery_charge_tangail' => '50',
            'delivery_charge_other' => '150',
            'free_delivery_threshold' => '2000',
            'bkash_number' => '01775153740',
            'bkash_type' => 'Personal',
            'nagad_number' => '01775153740',
            'nagad_type' => 'Personal',
            'rocket_number' => '01775153740',
            'rocket_type' => 'Personal',
            'facebook_page' => 'https://facebook.com',
            'instagram_page' => 'https://instagram.com',
            'store_address' => 'Main Road, Tangail Sadar, Tangail - 1900, Bangladesh',
            'currency' => '৳',
            'currency_code' => 'BDT',
            'announcement_bar' => '🔥 Special Discount: Use coupon code FASHION10 to get 10% OFF! Fast Cash On Delivery available across Bangladesh.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }
    }
}
