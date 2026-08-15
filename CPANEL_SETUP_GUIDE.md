# 🚀 cPanel 1-Click Deployment Guide for OnlineBdMart

এই জিপ ফাইলটি বিশেষভাবে **cPanel, DirectAdmin, Apache, Nginx এবং LiteSpeed সার্ভারে সরাসরি আপলোড ও ১-ক্লিকে রান করার জন্য প্রস্তুত (cPanel Ready)** করে তৈরি করা হয়েছে।

---

## ⚡ পদ্ধতি ১: সরাসরি `public_html` এ আপলোড (সবচেয়ে সহজ ১-ক্লিক পদ্ধতি)

প্রজেক্টের রুটে স্বয়ংক্রিয় `.htaccess` কনফিগার করা আছে যা রিকোয়েস্টগুলোকে স্বয়ংক্রিয়ভাবে `public/index.php` এ রিডাইরেক্ট করে।

1. **cPanel এ লগইন করুন**: আপনার হোস্টিং cPanel এ প্রবেশ করে **File Manager** ওপেন করুন।
2. **`public_html` এ যান**: `public_html` ফোল্ডারে প্রবেশ করুন।
3. **Upload & Extract**: 
   - `OnlineBdMart-cPanel-Ready.zip` ফাইলটি আপলোড করুন।
   - রাইট ক্লিক করে **Extract** করুন।
4. **ডাটাবেস তৈরি ও ইম্পোর্ট (MySQL Setup)**:
   - cPanel থেকে **MySQL Database Wizard** এ গিয়ে একটি ডাটাবেস এবং ইউজার তৈরি করুন (সবগুলো Permissions দিন)।
   - **phpMyAdmin** ওপেন করে আপনার ডাটাবেস সিলেক্ট করুন এবং **Import** ট্যাবে গিয়ে প্রজেক্টের `database/onlinebdmart.sql` ফাইলটি সিলেক্ট করে **Import** বাটনে ক্লিক করুন।
5. **`.env` ফাইল এডিট করুন**:
   - `public_html` ফোল্ডারের ভেতরে `.env` ফাইলটি এডিট করুন:
   ```env
   APP_NAME="OnlineBdMart"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=আপনার_ডাটাবেসের_নাম
   DB_USERNAME=আপনার_ডাটাবেসের_ইউজার
   DB_PASSWORD=আপনার_ডাটাবেসের_পাসওয়ার্ড
   ```
6. **ব্যস!** আপনার ওয়েবসাইট এবং সিকিউর অ্যাডমিন প্যানেল সাথে সাথে লাইভ হয়ে যাবে!
   - গ্রাহক ওয়েবসাইট: `https://yourdomain.com`
   - সিকিউর অ্যাডমিন লগইন: `https://yourdomain.com/admin-panel/login`
   - ডিফল্ট লগইন: `admin` / `password`

---

## 🔒 পদ্ধতি ২: Standard Laravel 2-Folder Setup (সর্বোচ্চ নিরাপত্তা পদ্ধতি)

1. cPanel এর রুট ডিরেক্টরিতে (যেমন `/home/yourusername/`) `laravel_app` নামে একটি ফোল্ডার তৈরি করুন।
2. জিপ ফাইলের সমস্ত ফাইল `laravel_app` এ এক্সট্রাক্ট করুন।
3. `laravel_app/public/` ফোল্ডারের ভেতরের সমস্ত ফাইল ও ফোল্ডার (`index.php`, `.htaccess`, `css`, `js`, `images`, `uploads`) কাট (Move) করে আপনার `public_html/` ফোল্ডারে পেস্ট করুন।
4. `public_html/index.php` ফাইলটি ওপেন করে দুটি পাথ আপডেট করুন:
   ```php
   require __DIR__.'/../laravel_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
   ```
5. `phpMyAdmin` এ গিয়ে `database/onlinebdmart.sql` ইম্পোর্ট করুন এবং `.env` এ ডাটাবেস তথ্য দিন।

---

## 🔑 অ্যাডমিন পোর্টাল ও ডিফল্ট লগইন

* **Admin Portal URL**: `https://yourdomain.com/admin-panel/login`
* **Default Username**: `admin`
* **Default Password**: `password`
* **Customer Sign In**: `https://yourdomain.com/login`
* **Live Order Tracking**: `https://yourdomain.com/track-order`
* **Wholesale Catalog**: `https://yourdomain.com/wholesale`

---

## 📱 টেলিগ্রাম অর্ডার কন্ট্রোল সিস্টেম চালু করার নিয়ম

1. টেলিগ্রামে `@BotFather` এ গিয়ে `/newbot` লিখে একটি টেলিগ্রাম বট তৈরি করুন এবং Bot Token কপি করুন।
2. আপনার অ্যাডমিন প্যানেল **`https://yourdomain.com/admin-panel/telegram`** এ প্রবেশ করে টোকেন ও আপনার চ্যাট আইডি পেস্ট করে সেভ করুন।
3. নতুন অর্ডার আসার সাথে সাথে আপনার টেলিগ্রামে ইনলাইন বাটন সহ চলে আসবে এবং টেলিগ্রাম থেকেই অর্ডার কনফার্ম, প্রসেসিং, শিপিং ও ক্যানসেল করা যাবে!
