# Taply — Digital vCard SaaS

Taply is a full-stack digital business card platform for professionals, creators, and teams. It combines a polished public profile, lead capture, appointments, payments, portfolios, analytics, subscriptions, custom domains, and an administration console.

## Product surfaces

| Surface | Vite preview | Laravel route |
|---|---|---|
| Marketing website | `/` | `/` |
| Public vCard demo | `/profile.html` | `/himel` |
| Member dashboard | `/dashboard.html` | `/dashboard` |
| Admin dashboard | `/admin.html` | `/admin` |

The standalone HTML routes make the complete UI easy to preview without PHP. The corresponding Blade views in `resources/views` are wired to Laravel routes for production use.

## Stack

- **Backend:** PHP 8.3+, Laravel 12, MySQL 8, Sanctum, database queues, Laravel Scheduler
- **Frontend:** semantic HTML5, Tailwind CSS 4, custom CSS, JavaScript, GSAP, AOS, Lucide icons
- **Auth / roles:** Laravel Breeze-compatible auth flow, Spatie Laravel Permission
- **Media:** Spatie Media Library for profile images, portfolios, galleries, and CV files
- **Payments:** Stripe SDK, PayPal SDK, plus manual bKash/Nagad verification records
- **Sharing:** Simple QR Code on Laravel and `qrcode` in the interactive frontend

## Implemented features

### Public card

- Profile photo, identity, about section, location, availability, and local time
- Phone, WhatsApp, email, website, and social links
- Services, portfolio gallery, payment methods, CV download, and QR code
- Downloadable `.vcf` contact card
- Functional share and payment dialogs
- Appointment booking interaction with date/time selection
- Mobile-first public profile layout

### Member dashboard

- Profile performance, links, leads, contact saves, and appointments
- Profile-completion checklist and recent activity
- Live card preview
- Working edit drawer and theme selector
- Share link and QR-code download actions

### Administration

- User, subscription, revenue, domain, and transaction summaries
- Payment-verification status
- User-management table
- Revenue breakdown and infrastructure health
- Role-protected Laravel route

### Backend architecture

- Domain models and relationships for all requested tables
- Form Requests for appointments and leads
- Repository abstraction for public card lookup
- Analytics and appointment services
- Queued owner/guest appointment notifications
- Sanctum-protected v1 card API
- Scheduler tasks for analytics retention, queue cleanup, and SSL renewal checks
- Rate-limited public lead and booking endpoints
- Seed data for plans, themes, roles, an admin, and the `/himel` demo card

## Database

Migrations cover:

`users`, `profiles`, `vcards`, `social_links`, `payment_methods`, `services`, `portfolios`, `galleries`, `cv_files`, `appointments`, `leads`, `subscriptions`, `plans`, `transactions`, `analytics`, `themes`, `custom_domains`, `coupons`, `settings`, `media`, `notifications`, `personal_access_tokens`, role/permission tables, queue tables, sessions, and cache.

Sensitive payment metadata uses Laravel's encrypted array cast. Analytics stores a keyed hash of the visitor IP rather than the raw address.

## Local frontend preview

Requirements: Node.js 20+.

```bash
npm install
npm run dev
```

Vite binds to `0.0.0.0` and accepts preview proxy hostnames. For a production bundle:

```bash
npm run build
```

## Laravel setup

Requirements: PHP 8.3+, Composer 2, MySQL 8, Node.js 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate

# Create the MySQL database named in .env, then:
php artisan migrate --seed
php artisan storage:link

npm install
npm run build
php artisan serve
```

Demo seed credentials (change these outside local development):

- Admin: `admin@taply.me` / `password`
- Member: `himel@studio.com` / `password`

## Queue and Scheduler

Appointment confirmation emails and dashboard notifications run on the queue:

```bash
php artisan queue:work --queue=default --tries=3
```

Production cron entry:

```cron
* * * * * cd /path/to/taply && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler prunes stale queue data and analytics, and checks custom-domain SSL renewal windows.

## API

Sanctum-authenticated endpoints:

```text
GET   /api/v1/user
GET   /api/v1/cards
GET   /api/v1/cards/{vcard}
PATCH /api/v1/cards/{vcard}
```

Public conversion endpoints:

```text
POST /cards/{vcard}/appointments
POST /cards/{vcard}/leads
```

## Project structure

```text
app/
├── Http/Controllers       Web, admin, auth, and API controllers
├── Http/Requests          Validated public form requests
├── Jobs                   Queued appointment delivery
├── Models                 Eloquent domain models
├── Notifications          Queued mail/database notifications
├── Repositories           Card data access contracts
└── Services               Analytics and booking workflows

resources/
├── css/app.css             Tailwind entry + product design system
├── js/                     Landing, profile, and dashboard behavior
└── views/                  Blade versions of all product surfaces

database/
├── factories
├── migrations             MySQL 8 schema
└── seeders                 Plans, roles, themes, and demo account
```

## Security notes

- Admin routes require the `admin` role.
- Member API endpoints are protected by Sanctum and ownership checks.
- Booking and lead endpoints use named rate limiters.
- Public analytics records an HMAC IP hash and does not persist raw IP addresses.
- Payment provider secrets belong in `.env` and are not committed.
