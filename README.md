# 💎 OnlineBdMart - Premium Fashion & Accessories eCommerce (Laravel 11 + Blade + Tailwind + Livewire/AJAX + MySQL)

একটি সম্পূর্ণ আধুনিক, মিনিমাল এবং অ্যানিমেটেড লাক্সারি ফ্যাশন অ্যান্ড অ্যাক্সেসরিজ ই-কমার্স ওয়েবসাইট ও অ্যাডমিন প্যানেল। 
(A complete, modern, minimal & animated luxury fashion eCommerce website and administrative dashboard built with Laravel, Blade, Tailwind CSS, AJAX & MySQL).

---

## 🌟 প্রধান ফিচারসমূহ (Key Features)

### 🛍️ কাস্টমার ফ্রন্টএন্ড (User Storefront):
- **মডার্ন ও অ্যানিমেটেড লাক্সারি ডিজাইন**: Plus Jakarta Sans ও Playfair Display টাইপোগ্রাফি, গ্লাস মরফিজম ইফেক্ট, মাইক্রো-অ্যানিমেশন।
- **হিরো স্লাইডার ও ব্যানার**: ডাইনামিক হিরো স্লাইডার ও লিমিটেড টাইম প্রমোশন ব্যানার।
- **ক্যাটাগরি ব্রাউজিং**: মেন কালেকশন, উইমেন কালেকশন, ওয়াচ অ্যান্ড টেক, লেদার গুডস, জুয়েলারি অ্যান্ড পারফিউম, নিউ অ্যারাইভালস।
- **ইন্টারেক্টিভ স্লাইডিং কার্ট ড্রয়ার (Cart Drawer)**: পেজ রিলোড ছাড়াই ডান পাশ থেকে স্লাইড হয়ে আসা লাইভ শপিং কার্ট, কোয়ান্টিটি ইনক্রিমেন্ট/ডিক্রিমেন্ট, ফ্রি ডেলিভারি প্রগ্রেস বার।
- **অ্যাডভান্সড শপ ও ফিল্টারিং**: ক্যাটাগরি, মূল্য ফিল্টার (Price Range Slider), কিওয়ার্ড সার্চ, লাইভ সাজেস্ট ড্রপডাউন এবং শর্টিং (Newest, Price Low to High, High to Low)।
- **প্রোডাক্ট ডিটেইল পেজ**: মাল্টি-ইমেজ থাম্বনেইল গ্যালারি, জুমিং, ইনস্ট্যান্ট স্টক স্ট্যাটাস, রিভিউ অ্যান্ড রেটিং সাবমিশন।
- **১-ক্লিক হোয়াটসঅ্যাপ অর্ডার (1-Click WhatsApp Order)**: সরাসরি প্রোডাক্ট নাম ও লিঙ্কসহ হোয়াটসঅ্যাপে এক ক্লিকে অর্ডার করার সুবিধা।
- **এক্সপ্রেস সিঙ্গেল-পেজ চেকআউট**:
  - জেলা সিলেক্ট করলে স্বয়ংক্রিয় ডেলিভারি চার্জ ক্যালকুলেশন (যেমন: টাঙ্গাইল জেলা ৳৫০, অন্যান্য জেলা ৳১৫০)।
  - ক্যাশ অন ডেলিভারি (Cash on Delivery / COD)।
  - বিকাশ (bKash), নগদ (Nagad), ও রকেট (Rocket) পেমেন্ট অ্যাকাউন্ট নম্বর ১-ক্লিক কপি ও TrxID ইনপুট।
  - কুপন কোড (যেমন: `FASHION10` দিলে ১০% ছাড়)।
- **অর্ডার সাকসেস ও লাইভ ট্র্যাকিং**:
  - প্রিন্ট করা যাবে এমন এ৪ সাইজের ইনভয়েস রিসিপ্ট (`/order/{id}/invoice`)।
  - অর্ডার আইডি ও ফোন নম্বর দিয়ে লাইভ টাইমলাইন স্ট্যাটাস ট্র্যাকিং (`/track-order`): Order Received -> Confirmed -> Packaging -> Shipped -> Delivered.
- **কনট্যাক্ট ও সাপোর্ট**: সরাসরি হটলাইন কল, হোয়াটসঅ্যাপ চ্যাট, সাপোর্ট ইমেইল এবং ইন্টারেক্টিভ FAQ অ্যাকর্ডিয়ন।

### 🛠️ অ্যাডমিন প্যানেল (Admin Panel):
- **ড্যাশবোর্ড অ্যানালিটিক্স**: টোটাল রেভিনিউ, টোটাল অর্ডার, পেন্ডিং অর্ডার, ডেলিভার্ড অর্ডার, লো স্টক অ্যালার্ট।
- **প্রোডাক্ট ম্যানেজমেন্ট (CRUD)**: নতুন প্রোডাক্ট তৈরি, ছবি আপলোড, ক্যাটাগরি অ্যাসাইন, স্টক ম্যানেজমেন্ট, ফিচারড টগল, এডিট ও ডিলিট।
- **ক্যাটাগরি ম্যানেজমেন্ট (CRUD)**: নতুন ক্যাটাগরি তৈরি, আইকন ও স্লাগ অটো-জেনারেট, প্রোডাক্ট কাউন্ট।
- **অর্ডার ম্যানেজমেন্ট**: স্ট্যাটাস ফিল্টার (Pending, Confirmed, Processing, Shipped, Delivered, Cancelled), ১-ক্লিক স্ট্যাটাস আপডেট, কাস্টমার ডিটেইলস মোডাল, CSV এক্সপোর্ট।
- **হিরো ব্যানার ম্যানেজার**: হোমপেজ স্লাইডার ইমেজ আপলোড, টাইটেল, সাবটাইটেল, বাটন লিঙ্ক ও স্ট্যাটাস টগল।
- **কুপন / ডিসকাউন্ট কোড**: পারসেন্টেজ বা ফিক্সড অ্যামাউন্ট ডিসকাউন্ট কুপন তৈরি ও ব্যবহারের লিমিট সেট।
- **সাইট সেটিংস**: স্টোর নাম, লোগো, ফোন নম্বর, হোয়াটসঅ্যাপ নম্বর, বিকাশ/নগদ/রকেট নম্বর, জেলা ভিত্তিক ডেলিভারি চার্জ পরিবর্তন।

---

## 🚀 লোকালহোস্টে সেটআপ করার নিয়ম (Localhost Setup Guide - XAMPP / Laragon)

### ১. ডাটাবেস তৈরি করুন:
1. আপনার `phpMyAdmin` বা MySQL এ প্রবেশ করুন।
2. `onlinebdmart` নামে একটি ডাটাবেস তৈরি করুন।
3. প্রজেক্টের `database/onlinebdmart.sql` ফাইলটি `phpMyAdmin` থেকে **Import** করুন (অথবা নিচের স্টেপ অনুযায়ী Laravel Migration ও Seeder চালান)।

### ২. ডিপেনডেন্সি ও কনফিগারেশন:
```bash
# প্রজেক্ট ফোল্ডারে প্রবেশ করুন
cd Fashion-Store

# কম্পোজার প্যাকেজ ইনস্টল করুন
composer install

# .env ফাইল তৈরি করুন (যদি না থাকে)
cp .env.example .env

# অ্যাপ্লিকেশন কি (Key) তৈরি করুন
php artisan key:generate

# ডাটাবেস মাইগ্রেশন ও সীডার চালান
php artisan migrate --seed

# স্টোরেজ সিমলিঙ্ক তৈরি করুন
php artisan storage:link
```

### ৩. লোকাল সার্ভার রান করুন:
```bash
php artisan serve
```
এরপর ব্রাউজারে প্রবেশ করুন:
- **গ্রাহক ফ্রন্টএন্ড (Storefront)**: `http://127.0.0.1:8000`
- **অ্যাডমিন লগইন (Admin Login)**: `http://127.0.0.1:8000/admin/login`

---

## 🔑 ডিফল্ট অ্যাডমিন ক্রেডেনশিয়ালস (Default Admin Login)

- **Login URL**: `/admin/login`
- **Username**: `admin`
- **Email**: `admin@onlinebdmart.com`
- **Password**: `password`

---

## 📁 প্রজেক্ট ফাইল স্ট্রাকচার (Project Structure)

```text
Fashion-Store/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── ShopController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ContactController.php
│   │   │   └── Admin/
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── ProductController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── OrderController.php
│   │   │       ├── BannerController.php
│   │   │       ├── CouponController.php
│   │   │       └── SettingController.php
│   │   └── Middleware/
│   │       └── AdminAuth.php
│   └── Models/
│       ├── Admin.php
│       ├── Category.php
│       ├── Product.php
│       ├── ProductImage.php
│       ├── Order.php
│       ├── OrderItem.php
│       ├── Setting.php
│       ├── Banner.php
│       ├── Coupon.php
│       └── Review.php
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── session.php
│   └── filesystems.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── onlinebdmart.sql
├── public/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── admin.blade.php
│       ├── home.blade.php
│       ├── shop/
│       ├── cart/
│       ├── checkout/
│       ├── orders/
│       ├── contact.blade.php
│       └── admin/
├── routes/
│   ├── web.php
│   └── api.php
├── composer.json
├── artisan
└── README.md
```

---

## 🌐 cPanel / Live Hosting এ আপলোড করার নিয়ম

1. সমস্ত প্রজেক্টের জিপ ফাইলটি আপনার cPanel File Manager এ আপলোড করুন।
2. MySQL Databases থেকে ডাটাবেস তৈরি করে `onlinebdmart.sql` ইম্পোর্ট করুন।
3. `.env` ফাইলে আপনার ডাটাবেস ইউজারনেম, পাসওয়ার্ড ও নাম যুক্ত করুন।
4. ব্যস! ওয়েবসাইট স্বয়ংক্রিয়ভাবে লাইভ কাজ করবে।

---
Developed for **OnlineBdMart** — Premium Luxury Accessories.
