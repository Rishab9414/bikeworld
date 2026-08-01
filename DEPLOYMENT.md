# BikeWorld — Hostinger Deployment Guide

Deploy Laravel 12 BikeWorld on **Hostinger shared hosting** (hPanel + `public_html`).

---

## What was prepared in this repo

| File | Purpose |
|------|---------|
| `.htaccess` (project root) | Routes traffic into `public/` and blocks sensitive folders |
| `public/.htaccess` | Standard Laravel front controller + upload limits |
| `.env.hostinger.example` | Production env template (MySQL, HTTPS, live keys) |
| Trusted proxies + HTTPS | App trusts Hostinger proxy and forces HTTPS in production |

---

## Requirements (Hostinger)

- PHP **8.2+** (hPanel → Advanced → PHP Configuration)
- Extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `gd` / `imagick`, `zip`
- MySQL database (hPanel → Databases)
- SSL enabled (hPanel → SSL — free Let's Encrypt)
- SSH access (recommended) for Composer / Artisan

---

## Part 1 — Prepare on your PC

### 1. Build frontend assets locally

Hostinger shared plans often lack Node.js. Build on your PC, then upload `public/build`:

```powershell
cd c:\rishi\bikeworld
npm install
npm run build
```

Confirm `public/build/manifest.json` exists.

### 2. Do not upload these

| Skip | Why |
|------|-----|
| `.env` | Create fresh on server from `.env.hostinger.example` |
| `node_modules/` | Not needed on server |
| `public/hot` | **Dev-only** — causes site to load CSS/JS from localhost and break production |
| `.git/` | Optional; skip if uploading ZIP/FTP |
| `tests/`, `.phpunit*` | Not needed in production |
| `storage/logs/*` | Keep folders, clear log files |

**Do upload** `vendor/` **or** run `composer install` on the server via SSH (preferred).

### 3. Zip for File Manager upload (optional)

```powershell
cd c:\rishi\bikeworld
# Prefer excluding node_modules and .env when zipping
```

Or use Git + Hostinger Git deployment / SSH `git clone`.

---

## Part 2 — Hostinger panel setup

### 4. PHP version

hPanel → **Advanced** → **PHP Configuration** → select **PHP 8.2** or **8.3**.

### 5. Create MySQL database

hPanel → **Databases** → **MySQL Databases**:

1. Create database (note full name, e.g. `u123456789_bikeworld`)
2. Create user + strong password
3. Assign user to database with **All privileges**

### 6. SSL

hPanel → **SSL** → install free certificate for your domain.

---

## Part 3 — Upload project

### Recommended layout (Hostinger official style)

Upload the **entire** Laravel project into:

```text
/home/uXXXXX/domains/yourdomain.com/public_html/
```

So you have:

```text
public_html/
├── .htaccess          ← root rewrite (already in repo)
├── .env               ← create on server (never commit)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/            ← Laravel public (index.php, build/, images/)
├── resources/
├── routes/
├── storage/
├── vendor/            ← or install via Composer
├── artisan
└── composer.json
```

The root `.htaccess` sends all requests to `public/` and blocks access to `.env`, `vendor`, etc.

### Upload methods

- **File Manager**: upload ZIP → Extract inside `public_html`
- **FTP/SFTP**: FileZilla → upload into `public_html`
- **SSH + Git**: `git clone` into `public_html` (or a subfolder, then move)

---

## Part 4 — Production `.env`

Via File Manager or SSH:

```bash
cd ~/domains/yourdomain.com/public_html
cp .env.hostinger.example .env
nano .env   # or edit in File Manager
```

Set at least:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=uXXXXX_yourdb
DB_USERNAME=uXXXXX_youruser
DB_PASSWORD=your_mysql_password

RAZORPAY_MOCK=false
# paste live Razorpay + Brevo + Delhivery secrets
```

Generate app key (SSH):

```bash
php artisan key:generate
```

Or copy your existing local `APP_KEY` into `.env` if you prefer to keep encrypted data compatible.

---

## Part 5 — SSH install commands

Enable SSH in hPanel if needed, then:

```bash
cd ~/domains/yourdomain.com/public_html

# PHP dependencies (skip if you already uploaded vendor/)
composer install --no-dev --optimize-autoloader

# Database
php artisan migrate --force
# optional first-time seed:
# php artisan db:seed --force

# Public storage symlink (product images)
php artisan storage:link

# Writable folders
chmod -R 775 storage bootstrap/cache

# Cache config/routes/views
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If `storage:link` fails on shared hosting, create the link manually:

```bash
ln -s ../storage/app/public public/storage
```

---

## Part 6 — Cron jobs (required)

hPanel → **Advanced** → **Cron Jobs**

Replace `uXXXXX` and `yourdomain.com` with your paths.

### Scheduler (every minute)

```text
* * * * * /usr/bin/php /home/uXXXXX/domains/yourdomain.com/public_html/artisan schedule:run
```

### Queue worker (every minute — shared hosting substitute for Supervisor)

```text
* * * * * /usr/bin/php /home/uXXXXX/domains/yourdomain.com/public_html/artisan queue:work database --stop-when-empty --tries=3 --max-time=50 --queue=default,deliveries,notifications
```

Emails, shipping jobs, and tracking sync depend on these crons.

---

## Part 7 — Webhooks

| Service | URL |
|---------|-----|
| Razorpay | `https://yourdomain.com/webhooks/razorpay` |
| Delhivery | `https://yourdomain.com/webhooks/delhivery` |

After setting webhook secrets in `.env`:

```bash
php artisan config:cache
```

---

## Part 8 — Post-deploy checklist

1. Open `https://yourdomain.com` — homepage loads
2. CSS/JS load (Vite build present under `public/build`)
3. Admin: `/admin` — change default password if seeded
4. Product images — upload or sync `storage/app/public`
5. Place a test order / Razorpay payment
6. Confirm order email (Brevo) and queue cron running
7. `/sitemap.xml` works
8. Confirm `APP_DEBUG=false`

---

## Updating the site later

On your PC:

```powershell
npm run build
git push   # or re-upload changed files + public/build
```

On Hostinger (SSH):

```bash
cd ~/domains/yourdomain.com/public_html
git pull origin main   # if using Git
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If you deploy by FTP: upload changed PHP files + `public/build` after each frontend change.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 error | `storage/logs/laravel.log`; ensure `storage` & `bootstrap/cache` are 775 |
| Blank page / no CSS | Upload `public/build` after `npm run build` locally; **delete `public/hot` on server** if present |
| Database error | Check hPanel MySQL name/user/password; `DB_HOST=localhost` |
| Mixed content / login loops | `APP_URL=https://...`, SSL on, then `php artisan config:cache` |
| Images 404 | `php artisan storage:link` or manual `public/storage` symlink |
| Emails not sending | Cron queue job + `BREVO_ENABLED=true` + valid API key |
| `.env` readable in browser | Root `.htaccess` must be present; never put `.env` inside `public/` |

---

## Security checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] HTTPS / SSL active
- [ ] Root `.htaccess` blocking `app`, `vendor`, `.env`
- [ ] Strong MySQL + admin passwords
- [ ] Live Razorpay keys only after SSL works
- [ ] `.env` never committed to Git

---

*BikeWorld · Hostinger shared hosting · July 2026*
