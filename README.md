# BikeWorld - Laravel E-Commerce

A Laravel 12 e-commerce application for selling bikes, e-bikes, and cycling accessories.

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Visit `http://localhost:8000`

## Admin Portal

Custom admin authentication (separate from storefront Breeze login).

**URL:** http://127.0.0.1:8000/admin

| Email               | Password      |
|---------------------|---------------|
| admin@bikeworld.com | Admin@12345   |

After login you are redirected to `/admin/dashboard`.

## Demo Accounts (Storefront)

| Role     | Email                  | Password |
|----------|------------------------|----------|
| Customer | customer@bikeworld.com | password |

## Features

- Product catalog with categories and search
- Shopping cart (guest and authenticated users)
- Checkout and order management
- User authentication (Laravel Breeze)
- Sample bike shop inventory seeded

## Tech Stack

- Laravel 12
- Laravel Breeze (Blade + Tailwind CSS)
- SQLite (default) — configure MySQL in `.env` for production

## Database

Default uses SQLite (`database/database.sqlite`). To switch to MySQL, update `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bikeworld
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate --seed`.
