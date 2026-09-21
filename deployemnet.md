# 🚀 MobiTrack ERP — Production Deployment & CI/CD Setup Guide

> 📌 **Note:** For the primary reference, see [deployment.md](file:///d:/karan/deployment.md).

A complete, production-grade guide for deploying **MobiTrack ERP (Laravel 10)** and setting up automated Continuous Integration / Continuous Deployment (CI/CD) pipelines using **GitHub Actions**.

---

## 📑 Table of Contents

1. [System Architecture & Overview](#1-system-architecture--overview)
2. [Prerequisites & Server Requirements](#2-prerequisites--server-requirements)
3. [Automated CI/CD Pipeline Setup (GitHub Actions)](#3-automated-cicd-pipeline-setup-github-actions)
   - [A. Generate SSH Key Pair](#a-generate-ssh-key-pair)
   - [B. Add Public Key to Server](#b-add-public-key-to-server)
   - [C. Configure GitHub Secrets](#c-configure-github-secrets)
   - [D. CI/CD Workflow (`deploy.yml`)](#d-cicd-workflow-deployyml)
4. [Target Server Setup (cPanel / DirectAdmin / Shared Hosting)](#4-target-server-setup-cpanel--directadmin--shared-hosting)
   - [A. Directory Layout (`public_html` + `app`)](#a-directory-layout-public_html--app)
   - [B. Database Creation](#b-database-creation)
   - [C. Production `.env` Configuration](#c-production-env-configuration)
   - [D. Directory Permissions](#d-directory-permissions)
5. [Target Server Setup (Linux VPS / Nginx / Apache)](#5-target-server-setup-linux-vps--nginx--apache)
   - [A. Nginx Server Block](#a-nginx-server-block)
   - [B. PHP-FPM & Permissions](#b-php-fpm--permissions)
6. [Initial Launch & First-Time Database Setup](#6-initial-launch--first-time-database-setup)
7. [Cron Jobs, Automation & Background Workers](#7-cron-jobs-automation--background-workers)
   - [A. Laravel Task Scheduler (Cron)](#a-laravel-task-scheduler-cron)
   - [B. Queue Worker (Supervisor for VPS)](#b-queue-worker-supervisor-for-vps)
8. [Performance Optimization Checklist](#8-performance-optimization-checklist)
9. [Troubleshooting & Maintenance Playbook](#9-troubleshooting--maintenance-playbook)

---

## 1. System Architecture & Overview

MobiTrack is built on **Laravel 10**, **PHP 8.2**, **MySQL 5.7+ / MariaDB 10.3+**, and uses **Laravel Mix / Tailwind CSS** for the frontend.

For shared hosting platforms (DirectAdmin, cPanel, Hostinger, YouStable), the deployment employs an enterprise-grade **Split Directory Architecture**:

```text
/home/username/domains/yourdomain.com/
├── public_html/                     <-- Web Root (Publicly accessible)
│   ├── .htaccess                   <-- URL rewriting & security rules
│   ├── index.php                   <-- Bootstrapper (points to ../app)
│   ├── css/                        <-- Compiled stylesheets
│   ├── js/                         <-- Compiled scripts & Vue bundles
│   ├── img/                        <-- Static images & logos
│   └── storage/ -> ../app/storage/app/public  <-- Public storage symlink
│
└── app/                             <-- Core Application (NOT accessible via browser)
    ├── .env                         <-- Production environment configuration
    ├── artisan                      <-- Laravel CLI command tool
    ├── app/                         <-- Models, Controllers, Policies, Services
    ├── bootstrap/                   <-- Framework bootstrap & cache
    ├── config/                      <-- App configs
    ├── database/                    <-- Migrations & Seeders
    ├── modules/                     <-- Custom business modules
    ├── resources/                   <-- Views (Blade), components
    ├── routes/                      <-- Web, API & Guest routes
    ├── storage/                     <-- Logs, file uploads, framework cache (775)
    └── vendor/                      <-- PHP Composer dependencies
```

### Security Benefits of Split Directory:
- **Zero Exposure**: Your `.env` file, database credentials, vendor code, and core files reside outside `public_html/`. Even if web server misconfigurations occur, visitors can never download your private environment files.

---

## 2. Prerequisites & Server Requirements

### Server Requirements
| Component | Minimum Version | Recommended |
|---|---|---|
| **OS** | Ubuntu 22.04 LTS / Debian 12 / CloudLinux / AlmaLinux 9 | Ubuntu 22.04 LTS |
| **PHP** | 8.2.0 or higher | PHP 8.2 |
| **Database** | MySQL 5.7+ or MariaDB 10.3+ | MariaDB 10.6+ |
| **Web Server** | Nginx 1.20+ or Apache 2.4+ (with `mod_rewrite`) | Nginx or LiteSpeed |
| **Node.js** | v18.x LTS or v20.x LTS | Node.js v20 LTS |
| **Composer** | v2.5+ | Latest Composer v2 |
| **SSH / Rsync** | OpenSSH client & server, `rsync` | Available on server |

### Required PHP Extensions:
Ensure these PHP extensions are enabled (via cPanel **Select PHP Version** &rarr; **Extensions** or `apt install`):
```text
bcmath, ctype, curl, dom, fileinfo, gd, intl, json, mbstring, openssl, pcre, pdo, pdo_mysql, tokenizer, xml, zip
```

---

## 3. Automated CI/CD Pipeline Setup (GitHub Actions)

Every push to `master` or `main` automatically builds frontend and backend dependencies, stages files, syncs them via SSH `rsync` to your server, runs migrations, and clears/refreshes application caches.

### A. Generate SSH Key Pair

On your local machine (or the target server terminal), generate a dedicated 4096-bit RSA deployment key:

```bash
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy@mobitrack" -f ~/.ssh/deploy_mobitrack -N ""
```

This generates two files:
- `deploy_mobitrack` (Private key — keep safe!)
- `deploy_mobitrack.pub` (Public key)

### B. Add Public Key to Server

Append the public key to your hosting server's `authorized_keys`:

```bash
# On your server (or via cPanel SSH Access Manager)
mkdir -p ~/.ssh
chmod 700 ~/.ssh
cat deploy_mobitrack.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

> 💡 **cPanel / DirectAdmin Tip**: You can also go to **SSH Access** &rarr; **Manage SSH Keys** &rarr; **Import Key**, paste `deploy_mobitrack.pub`, and click **Authorize**.

### C. Configure GitHub Secrets

1. Open your repository on GitHub: `https://github.com/<owner>/<repo>/settings/secrets/actions`
2. Click **New repository secret** and add the following:

| Secret Name | Required? | Description | Example Value |
|---|---|---|---|
| `SSH_HOST` | **Required** | Server IP address or Hostname | `123.45.67.89` or `phonefixazamgarh.com` |
| `SSH_USER` | **Required** | SSH Username for hosting account | `u1234567` or `deployer` |
| `SSH_PRIVATE_KEY`| **Required** | Entire contents of `deploy_mobitrack` (private key) | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `SSH_PORT` | Optional | SSH Port (defaults to `22` if not set) | `22` (or hosting port like `2222`) |
| `SSH_TARGET_DIR` | Optional | Root path where `public_html` & `app` reside | `domains/phonefixazamgarh.com` or `./` |

---

### D. CI/CD Workflow (`.github/workflows/deploy.yml`)

The repository already contains the production deployment workflow at [.github/workflows/deploy.yml](file:///d:/karan/.github/workflows/deploy.yml).

Here is the operational workflow overview:

```mermaid
flowchart TD
    A[git push to master/main] --> B[GitHub Actions Runner: Ubuntu-latest]
    B --> C[Setup PHP 8.2 + Extensions]
    C --> D[composer install --no-dev --optimize-autoloader]
    D --> E[Stage: Split files into deploy/public_html & deploy/app]
    E --> F[Patch public_html/index.php paths to ../app/]
    F --> G[Authenticate SSH using secrets.SSH_PRIVATE_KEY]
    G --> H[Sync files via Incremental rsync to server]
    H --> I[Post-Deploy on Remote Server]
    I --> J[php artisan migrate --force]
    I --> K[php artisan db:seed --class=MobileShopRbacSeeder]
    I --> L[php artisan mobileshop:setup-admin]
    I --> M[php artisan optimize:clear && config:cache && route:cache && view:cache]
    M --> N[Deployment Complete & Zero-Downtime Live!]
```

#### Automatic Execution & Manual Workflow Triggers:
- **Every Git Push / Deploy**: Automatically runs `php artisan migrate --force`, seeds core RBAC & Admin accounts (`MobileShopRbacSeeder`), configures admin credentials (`mobileshop:setup-admin`), and warms production caches.
- **Manual Workflow Trigger**:
  1. Navigate to **Actions** &rarr; **Deploy to Shared Hosting via SSH** on GitHub.
  2. Click **Run workflow**.
  3. Optionally toggle `seed_catalog` to `true` to seed the curated parts, displays, batteries & accessories catalog.

---

## 4. Target Server Setup (cPanel / DirectAdmin / Shared Hosting)

### A. Directory Layout (`public_html` + `app`)

Create the application directory parallel to `public_html` on your server:

```bash
# In your domain root (e.g., /home/username/domains/yourdomain.com/)
mkdir -p app
mkdir -p public_html
```

### B. Database Creation

1. In your hosting panel, open **MySQL Management** or **MySQL Database Wizard**.
2. Create a new database: e.g., `user_mobitrack`.
3. Create a database user: e.g., `user_dbadmin` with a strong password.
4. Add the user to the database and grant **ALL PRIVILEGES**.

### C. Production `.env` Configuration

In your server's `app/` folder (`/home/username/domains/yourdomain.com/app/.env`), create the `.env` file:

```ini
APP_NAME=MobiTrack
APP_ENV=production
APP_KEY=base64:GENERATE_VIA_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=user_mobitrack
DB_USERNAME=user_dbadmin
DB_PASSWORD=YourStrongDatabasePassword123!

# Session & Cache (File driver for shared hosting, Redis for VPS)
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Multi-tenancy & Business defaults
AK_COMPANY_ID=1
DEFAULT_LOCALE=en-US
DEFAULT_CURRENCY=INR
```

> 🔒 **Security Guarantee**: The GitHub Actions deployment script explicitly excludes `.env*` files during rsync, ensuring your server credentials are **never** overwritten or wiped out during git pushes.

### D. Directory Permissions

Set proper write permissions for runtime storage and cache:

```bash
cd /home/username/domains/yourdomain.com/app
chmod -R 775 storage bootstrap/cache
chmod -R 755 public_html
```

---

## 5. Target Server Setup (Linux VPS / Nginx / Apache)

If you are hosting on an Ubuntu/Debian VPS with Nginx and PHP-FPM:

### A. Nginx Server Block

Create `/etc/nginx/sites-available/mobitrack.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    # SSL Certificates (Certbot / Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    root /var/www/mobitrack/public_html;
    index index.php index.html;

    charset utf-8;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Block direct access to hidden files (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Block direct access to protected extensions
    location ~ \.(env|log|sql|bak|sh)$ {
        deny all;
    }

    # Pass PHP to PHP-FPM 8.2
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 16 16k;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 365d;
        add_header Cache-Control "public, no-transform";
    }
}
```

Enable site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/mobitrack.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 6. Initial Launch & First-Time Database Setup

When launching for the very first time on the server, execute these commands inside the `app/` folder via SSH or hosting Terminal:

```bash
cd /home/username/domains/yourdomain.com/app

# 1. Generate Application Encryption Key
php artisan key:generate --force

# 2. Run Database Migrations
php artisan migrate --force

# 3. Initialize Admin & System Accounts
php artisan mobileshop:setup-admin

# 4. Seed RBAC Roles & Permissions
php artisan db:seed --class="Database\Seeds\MobileShopRbacSeeder" --force

# 5. (Optional) Seed Sample/Demo Data if needed
# php artisan db:seed --class="Database\Seeds\MobileShopDemoSeeder" --force

# 6. Create Public Storage Symlink
php artisan storage:link

# 7. Generate Production Performance Caches
php artisan optimize
```

---

## 7. Cron Jobs, Automation & Background Workers

### A. Laravel Task Scheduler (Cron)

Laravel needs one master cron job that fires every minute to handle invoice overdue checks, low-stock notifications, warranty expirations, and scheduled database backups.

In cPanel or DirectAdmin &rarr; **Cron Jobs**, add:

```bash
* * * * * /usr/local/bin/php /home/username/domains/yourdomain.com/app/artisan schedule:run >> /dev/null 2>&1
```

> 📌 *Note: Verify your server's exact PHP path using `which php` in terminal. It is typically `/usr/local/bin/php`, `/usr/bin/php8.2`, or `/usr/bin/php`.*

### B. Queue Worker (Supervisor for VPS)

If your `.env` is configured with `QUEUE_CONNECTION=database` or `QUEUE_CONNECTION=redis`, manage the worker using Supervisor:

Create `/etc/supervisor/conf.d/mobitrack-worker.conf`:

```ini
[program:mobitrack-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mobitrack/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/mobitrack/app/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mobitrack-worker:*
```

---

## 8. Performance Optimization Checklist

To ensure maximum speed, snappy invoice generation, and low server resource usage, the deployment pipeline executes the following production optimizations:

```bash
# 1. Cache configuration files into a single bundle
php artisan config:cache

# 2. Cache application routes into a fast lookup tree
php artisan route:cache

# 3. Precompile Blade templates
php artisan view:cache

# 4. Precompile event/listener mappings
php artisan event:cache

# 5. Optimize Composer Autoloader
composer dump-autoload --optimize --no-dev --classmap-authoritative
```

To clear all caches in case of updates:
```bash
php artisan optimize:clear
```

---

## 9. Troubleshooting & Maintenance Playbook

| Symptom / Error | Root Cause | Resolution |
|---|---|---|
| **HTTP 500 Internal Server Error** | Missing `.env`, missing `APP_KEY`, or file permission lock. | 1. Check `/app/storage/logs/laravel.log`.<br>2. Run `php artisan key:generate`.<br>3. Verify `.env` exists in `app/`. |
| **Blank White Screen** | Server cannot write to `storage/` or `bootstrap/cache/`. | Run `chmod -R 775 storage bootstrap/cache`. |
| **Database Connection Refused** | Hostname incorrect or credentials mismatch. | On shared hosts, change `DB_HOST=127.0.0.1` to `DB_HOST=localhost` or verify user privileges. |
| **404 on Internal Pages / Routes** | Apache `mod_rewrite` is disabled or `.htaccess` is missing. | Ensure `.htaccess` exists in `public_html/` and Apache has `AllowOverride All` enabled. |
| **GitHub Action: SSH Connection Timeout** | Incorrect `SSH_PORT`, IP blocked by server firewall, or SSH disabled on host. | Verify port in GitHub secret `SSH_PORT`. Ensure the GitHub runner IP is not blacklisted by hosting fail2ban / CSF. |
| **GitHub Action: `rsync: command not found`** | Hosting server does not have `rsync` installed. | The workflow automatically falls back to streaming `.tar.gz` over SSH. No manual action required. |
| **CSS / JS not updating after push** | Browser caching or stale compiled assets. | Hard refresh with `Ctrl + F5` or run `npm run production` to generate fresh hashes. |
| **Storage images / PDFs 404** | Broken symlink between `public_html/storage` and `app/storage/app/public`. | Run `rm -rf public_html/storage && cd app && php artisan storage:link`. |

---

## 10. Summary Quick Reference

- **Trigger Deploy**: Just push to `master`:
  ```bash
  git add .
  git commit -m "feat: New updates"
  git push origin master
  ```
- **Inspect Deployment Logs**: [GitHub Actions Tab](https://github.com/altmash21/mm/actions)
- **Manual Cache Refresh**:
  ```bash
  cd app && php artisan optimize
  ```
