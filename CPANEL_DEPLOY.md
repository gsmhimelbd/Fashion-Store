# Putul Host / cPanel-এ Taply Deploy

এই প্যাকেজে production frontend আগে থেকেই build করা আছে। Server-এ Node.js চালানোর প্রয়োজন নেই। তবে Laravel dependencies install করার জন্য cPanel Terminal/SSH-তে Composer থাকতে হবে।

## ১. Hosting requirements যাচাই

Putul Host support-কে প্রয়োজনে জিজ্ঞেস করুন:

- PHP 8.3 বা নতুন version
- MySQL 8
- Composer 2
- cPanel Terminal অথবা SSH
- Domain document root পরিবর্তনের সুবিধা
- PHP extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`, `zip`, `gd`

## ২. ZIP upload ও extract

1. cPanel → **File Manager** খুলুন।
2. Home directory-তে যান—`public_html`-এর ভেতরে নয়।
3. `taply-cpanel-deploy.zip` upload করুন।
4. ZIP select করে **Extract** চাপুন।
5. Extract হলে `/home/CPANEL_USER/taply/`-এর মতো project folder পাবেন।

## ৩. Domain document root

cPanel → **Domains** → domain-এর **Manage** থেকে Document Root দিন:

```text
/home/CPANEL_USER/taply/public
```

`CPANEL_USER`-এর জায়গায় আপনার আসল cPanel username ব্যবহার করুন।

### Document Root বদলানো না গেলে

- Project `/home/CPANEL_USER/taply`-এ রাখুন।
- `taply/public/` folder-এর ভেতরের সব file `public_html/`-এ copy করুন।
- `public_html/index.php`-এ path পরিবর্তন করুন:

```php
require __DIR__.'/../taply/vendor/autoload.php';
$app = require_once __DIR__.'/../taply/bootstrap/app.php';
```

- `taply/public/build`, `images`, `files`, এবং `.htaccess`-ও `public_html`-এ থাকতে হবে।

## ৪. PHP version

cPanel → **MultiPHP Manager** → domain select → PHP **8.3** নির্বাচন করুন।

## ৫. MySQL database

cPanel → **MySQL Database Wizard**:

1. একটি database তৈরি করুন, যেমন `CPANEL_USER_taply`।
2. একটি database user এবং strong password তৈরি করুন।
3. User-কে database-এর **ALL PRIVILEGES** দিন।

## ৬. `.env` তৈরি

File Manager-এ `.env.example` copy করে `.env` নাম দিন। তারপর অন্তত এগুলো edit করুন:

```dotenv
APP_NAME=Taply
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=CPANEL_USER_taply
DB_USERNAME=CPANEL_USER_dbuser
DB_PASSWORD=YOUR_STRONG_DATABASE_PASSWORD

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
FILESYSTEM_DISK=public
```

## ৭. Terminal commands

cPanel → **Terminal** খুলে চালান:

```bash
cd ~/taply
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
chmod -R 775 storage bootstrap/cache
```

Project folder-এর নাম আলাদা হলে `cd ~/taply` পরিবর্তন করুন।

## ৮. Cron jobs

cPanel → **Cron Jobs** থেকে প্রতি মিনিটে দুটি command দিন:

```cron
* * * * * /usr/local/bin/php /home/CPANEL_USER/taply/artisan schedule:run >> /dev/null 2>&1
```

```cron
* * * * * /usr/local/bin/php /home/CPANEL_USER/taply/artisan queue:work --stop-when-empty --tries=3 >> /dev/null 2>&1
```

`which php` চালিয়ে PHP path আলাদা পেলে `/usr/local/bin/php` বদলান।

## ৯. Test URLs

- Website: `https://yourdomain.com`
- Demo vCard: `https://yourdomain.com/himel`
- Login: `https://yourdomain.com/login`
- Admin: `https://yourdomain.com/admin`

Seeded local/demo credentials:

- Admin: `admin@taply.me` / `password`
- Member: `himel@studio.com` / `password`

Deploy করার পর সঙ্গে সঙ্গে password পরিবর্তন করুন।

## Terminal/Composer না থাকলে

শুধু File Manager-এ source ZIP upload করলে Laravel চলবে না, কারণ `vendor/` dependencies প্রয়োজন। Putul Host support-কে এই message পাঠান:

> “আমার Laravel 12 project deploy করতে PHP 8.3, Composer 2, SSH/cPanel Terminal এবং document root project-এর public folder-এ point করার সুবিধা দরকার। এগুলো enable করে দিন।”
