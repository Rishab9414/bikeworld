# BikeWorld — Production Deployment Guide

Step-by-step guide to deploy BikeWorld (Laravel 12) from local development to a live server.

---

## Before you start

| Item | Example |
|------|---------|
| Domain | `bikeworld.in` or `www.bikeworld.in` |
| Server | VPS (Ubuntu 22/24) or cPanel hosting with PHP 8.2+ |
| Database | **MySQL** (do not use SQLite in production) |
| SSL | Free via Let's Encrypt |
| Git repo | GitHub / GitLab / Bitbucket |

**Requirements on server:** PHP 8.2+, Composer, Node.js 18+, MySQL 8+, Nginx (or Apache), Supervisor (for queues).

---

## Part 1 — On your local PC (before push)

### Step 1: Commit code to Git

```bash
cd c:\rishi\bikeworld
git status
```

Confirm `.env` is **not** listed (it must never be committed).

```bash
git add .
git commit -m "Production ready"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/bikeworld.git
git push -u origin main
```

Replace the Git URL with your real repository.

### Step 2: Save all secrets (do not push)

Copy these from your local `.env` into a password manager:

- `APP_KEY`
- `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, `RAZORPAY_WEBHOOK_SECRET`
- `DELHIVERY_API_TOKEN`, `DELHIVERY_CLIENT_NAME`, pickup address fields
- `BREVO_API_KEY`
- MySQL password (create on server)
- Admin login credentials

### Step 3: Verify frontend build (optional)

```bash
npm install
npm run build
```

`public/build` is gitignored — you will run `npm run build` again on the server.

### Step 4: Plan your data

**Option A (recommended for first launch):** Run migrations + seeders on the server, then add products via admin.

**Option B:** Export local data and import into MySQL (advanced). Product images must be uploaded separately (see Part 5).

---

## Part 2 — Server setup (VPS — Ubuntu)

SSH into your server:

```bash
ssh root@YOUR_SERVER_IP
```

### Step 5: Update system

```bash
apt update && apt upgrade -y
```

### Step 6: Install packages

```bash
apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath unzip git curl
```

Install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Install Node.js:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

### Step 7: Create MySQL database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE bikeworld CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bikeworld'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON bikeworld.* TO 'bikeworld'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 8: Clone project

```bash
cd /var/www
git clone https://github.com/YOUR_USERNAME/bikeworld.git
cd bikeworld
```

### Step 9: Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Step 10: Create production `.env`

```bash
cp .env.example .env
nano .env
```

Set these values (edit line by line):

```env
APP_NAME=BikeWorld
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bikeworld
DB_USERNAME=bikeworld
DB_PASSWORD=STRONG_PASSWORD_HERE

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

BREVO_ENABLED=true
BREVO_TRANSPORT=api
BREVO_API_KEY=xkeysib-your-key-here
BREVO_SENDER_EMAIL=bikeworld707@gmail.com
BREVO_SENDER_NAME=BikeWorld

RAZORPAY_KEY_ID=rzp_live_xxxxx
RAZORPAY_KEY_SECRET=your_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
RAZORPAY_MOCK=false
RAZORPAY_CURRENCY=INR
RAZORPAY_COMPANY_NAME=BikeWorld

DELHIVERY_MOCK=false
DELHIVERY_API_TOKEN=your_delhivery_token
DELHIVERY_CLIENT_NAME=your_client_name
DELHIVERY_PICKUP_LOCATION=BikeWorld Warehouse
DELHIVERY_PICKUP_ADDRESS=your_warehouse_address
DELHIVERY_PICKUP_CITY=your_city
DELHIVERY_PICKUP_STATE=your_state
DELHIVERY_PICKUP_PIN=your_pincode
DELHIVERY_PICKUP_PHONE=9743663260

STORE_SUPPORT_EMAIL=bikeworld707@gmail.com
STORE_SUPPORT_PHONE=9743663260

SEO_SITE_NAME=BikeWorld
SEO_ROBOTS=index,follow
```

Save: `Ctrl+O`, Enter, `Ctrl+X`.

### Step 11: Generate application key

```bash
php artisan key:generate
```

### Step 12: Run migrations

```bash
php artisan migrate --force
```

Optional — seed admin and sample data:

```bash
php artisan db:seed --force
```

Default admin (if seeded): `admin@bikeworld.com` / `Admin@12345` — **change immediately on live.**

### Step 13: Build frontend assets

```bash
npm install
npm run build
```

### Step 14: Storage link (product images)

```bash
php artisan storage:link
```

### Step 15: Folder permissions

```bash
chown -R www-data:www-data /var/www/bikeworld
chmod -R 755 /var/www/bikeworld
chmod -R 775 /var/www/bikeworld/storage
chmod -R 775 /var/www/bikeworld/bootstrap/cache
```

### Step 16: Optimize for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Part 3 — Nginx configuration

### Step 17: Create site config

```bash
nano /etc/nginx/sites-available/bikeworld
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/bikeworld/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 20M;
}
```

Enable and reload:

```bash
ln -s /etc/nginx/sites-available/bikeworld /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### Step 18: SSL (HTTPS) — required for Razorpay

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Update `.env`:

```env
APP_URL=https://yourdomain.com
```

Then:

```bash
php artisan config:cache
```

---

## Part 4 — Queue worker & scheduler

BikeWorld uses background jobs for emails, shipping, invoices, and tracking sync. Both must run on production.

### Step 19: Queue worker (Supervisor)

```bash
apt install supervisor -y
nano /etc/supervisor/conf.d/bikeworld-worker.conf
```

```ini
[program:bikeworld-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bikeworld/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --queue=default,deliveries,notifications
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/bikeworld/storage/logs/worker.log
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start bikeworld-worker:*
```

Check status:

```bash
supervisorctl status
```

### Step 20: Cron (scheduler)

Tracking sync runs every 30 minutes via Laravel scheduler.

```bash
crontab -e -u www-data
```

Add:

```cron
* * * * * cd /var/www/bikeworld && php artisan schedule:run >> /dev/null 2>&1
```

---

## Part 5 — Upload product images from local

Images are stored in `storage/app/public/` (not in Git).

**On local PC (PowerShell):**

```powershell
cd c:\rishi\bikeworld
tar -czf storage-uploads.tar.gz storage/app/public
scp storage-uploads.tar.gz root@YOUR_SERVER_IP:/var/www/bikeworld/
```

**On server:**

```bash
cd /var/www/bikeworld
tar -xzf storage-uploads.tar.gz
chown -R www-data:www-data storage
php artisan storage:link
```

---

## Part 6 — Webhooks

### Razorpay

1. Open [Razorpay Dashboard](https://dashboard.razorpay.com) → **Settings** → **Webhooks**
2. Add URL: `https://yourdomain.com/webhooks/razorpay`
3. Copy webhook secret → `RAZORPAY_WEBHOOK_SECRET` in `.env`
4. Run: `php artisan config:cache`

### Delhivery

Webhook URL: `https://yourdomain.com/webhooks/delhivery`

Configure in your Delhivery client dashboard if tracking callbacks are enabled.

---

## Part 7 — Post-deploy checklist

| # | Test | URL |
|---|------|-----|
| 1 | Homepage | `https://yourdomain.com` |
| 2 | Admin login | `https://yourdomain.com/admin` |
| 3 | Product images | Any product page |
| 4 | Search | `/search?q=helmet` |
| 5 | Blog | `/blog` |
| 6 | Cart + checkout | Full order flow |
| 7 | Razorpay payment | Test payment |
| 8 | Order email | Place order, check inbox |
| 9 | Sitemap | `/sitemap.xml` |
| 10 | Queue worker | `supervisorctl status` |

---

## Part 8 — Deploying updates (after initial launch)

Run on the server whenever you push new code:

```bash
cd /var/www/bikeworld
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
supervisorctl restart bikeworld-worker:*
```

---

## Shared hosting (cPanel)

If you use cPanel instead of a VPS:

1. Upload project via Git or FTP to e.g. `/home/username/bikeworld`
2. Set domain **document root** to `bikeworld/public` (not the project root)
3. Create MySQL database in cPanel → update `.env`
4. SSH or Terminal: `composer install --no-dev`, `php artisan migrate --force`, `npm run build`
5. Cron job: `* * * * * php /home/username/bikeworld/artisan schedule:run`
6. Queue: use host's Supervisor if available, or a cron running `queue:work` every minute

---

## Security checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `.env` never committed to Git
- [ ] HTTPS enabled (SSL certificate)
- [ ] `RAZORPAY_MOCK=false` only when ready for live payments
- [ ] Admin password changed after first login
- [ ] Brevo sender email verified in Brevo dashboard
- [ ] File permissions: `storage/` and `bootstrap/cache/` writable by web server only

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 error | Check `storage/logs/laravel.log` |
| Images not showing | Run `php artisan storage:link` |
| Emails not sending | Check `BREVO_ENABLED=true`, queue worker running |
| Orders stuck | `supervisorctl status` — restart worker |
| Payment fails | Confirm HTTPS + live Razorpay keys + webhook URL |
| Config changes ignored | Run `php artisan config:cache` after `.env` edits |

---

## Quick reference — processes that must run on production

| Process | Command |
|---------|---------|
| Web server | Nginx + PHP-FPM |
| Queue worker | `php artisan queue:work database --queue=default,deliveries,notifications` |
| Scheduler | Cron: `php artisan schedule:run` every minute |

---

*Last updated: July 2026 — BikeWorld Laravel 12*
