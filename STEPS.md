# 🚀 MobiTrack ERP — Shared Hosting CI/CD Deployment Guide

This guide explains how to set up continuous deployment for **MobiTrack ERP (Laravel 10)** on shared hosting using GitHub Actions with the split **`public_html` + `app`** folder architecture.

---

## 📁 Target Architecture on Your Shared Host

```text
domain.com/
├── public_html/                     <-- Web Root (Public Web Files)
│   ├── .htaccess
│   ├── index.php                   <-- Automatically points to ../app/
│   ├── css/
│   ├── js/
│   ├── img/
│   └── fonts/
│
└── app/                             <-- Core Laravel (Protected & Inaccessible from Web)
    ├── .env                         <-- Production environment file
    ├── artisan                      <-- CLI artisan
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/                     <-- Needs write permissions (775)
    └── vendor/
```

---

## ⚙️ Step 1: Configure GitHub Repository Secrets

1. Open your repository on [GitHub.com](https://github.com).
2. Go to **Settings** &rarr; **Secrets and variables** &rarr; **Actions**.
3. Click **New repository secret** and add the following 4 secrets:

| Secret Name | Description | Example Value |
|---|---|---|
| `FTP_SERVER` | Your shared hosting FTP Host / IP | `ftp.yourdomain.com` or `123.45.67.89` |
| `FTP_USERNAME` | Your FTP account username | `deployer@yourdomain.com` |
| `FTP_PASSWORD` | Your FTP account password | `YourStrongPassword!123` |
| `FTP_REMOTE_DIR` | Directory on FTP where `public_html` & `app` reside | `./` or `domain.com/` |

> 💡 **Tip:** If your FTP user logs in directly inside `domain.com/`, leave `FTP_REMOTE_DIR` as `./`. If it logs in at `/home/username/`, set it to `domain.com/` (or your domain folder).

---

## 🗄️ Step 2: Set Up Database on Shared Hosting

1. Log into your hosting panel (**cPanel**, **Hostinger**, etc.).
2. Open **MySQL Databases** (or **Database Wizard**):
   - Create a database: e.g. `cpaneluser_mobitrack`
   - Create a user: e.g. `cpaneluser_dbuser`
   - Assign user to the database with **ALL PRIVILEGES**.
3. Note down the Database Name, User, and Password.

---

## 📝 Step 3: Create the Production `.env` File

1. In your hosting **File Manager**, create the `app/` directory if it doesn't exist yet: `domain.com/app/`.
2. Inside `domain.com/app/`, create a file named `.env`:
3. Paste the following configuration (replace with your domain & database details):

```ini
APP_NAME=MobiTrack
APP_ENV=production
APP_KEY=base64:your-generated-app-key-here
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_mobitrack
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=your_actual_db_password

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_DRIVER=file

# Multi-tenancy / Mail / SMS settings (as required)
```

> 🔒 **Security:** The deployment script ignores `.env` files so your server passwords will **never** be overwritten by Git pushes.

---

## 🗃️ Step 4: Initial Database Import

Choose whichever is easiest for you:

### Option A: Via phpMyAdmin (Easiest & Fastest)
1. Export your local MySQL database using phpMyAdmin or HeidiSQL as `.sql`.
2. In your hosting cPanel, open **phpMyAdmin**, select your new database, and click **Import**.
3. Select your `.sql` file and click **Go**.

### Option B: Via cPanel Terminal
In cPanel &rarr; **Terminal**, run:
```bash
cd domain.com/app
php artisan migrate --force
php artisan db:seed --class=MobileShopRbacSeeder --force
php artisan db:seed --class=MobileShopDemoSeeder --force
```

---

## 🔐 Step 5: Verify Permissions on Server

Ensure the Laravel storage and bootstrap cache folders are writable by the server:
- `domain.com/app/storage` &rarr; `775` (or `755`)
- `domain.com/app/bootstrap/cache` &rarr; `775` (or `755`)

In cPanel File Manager:
1. Right-click on `storage` folder inside `domain.com/app/`.
2. Click **Change Permissions** and ensure **User**, **Group**, and **World** have Read & Execute, and User/Group have Write.

---

## ⏱️ Step 6: Set Up Background Scheduled Tasks (Cron)

In cPanel &rarr; **Cron Jobs**:
- Choose interval: **Once Per Minute** (`* * * * *`)
- Enter command:
```bash
/usr/local/bin/php /home/username/domain.com/app/artisan schedule:run >> /dev/null 2>&1
```
*(Replace `/home/username/domain.com` with your hosting home path).*

---

## 🚀 Step 7: Deploy via Git Push!

Now everything is configured. Whenever you push code from your computer:

```bash
git add .
git commit -m "Deploy latest changes"
git push origin master
```

### What happens automatically:
1. GitHub Actions triggers `.github/workflows/deploy.yml`.
2. In the cloud, it:
   - Installs PHP production dependencies (`composer install --no-dev`)
   - Compiles JS/CSS assets (`npm run production`)
   - Prepares `deploy/public_html` and `deploy/app`
   - Configures `public_html/index.php` to link to `../app/`
   - Uploads only modified files via FTP.
3. Your live site is updated within **1–2 minutes** with zero downtime!

---

## 🛠️ Troubleshooting

| Issue | Cause | Solution |
|---|---|---|
| **500 Internal Server Error** | Missing `.env` or bad path | Check `domain.com/app/storage/logs/laravel.log` for details. Verify `domain.com/app/.env` exists. |
| **Blank White Screen** | Permissions issue | Set `storage/` and `bootstrap/cache/` to `775` in cPanel File Manager. |
| **FTP Deploy Action Fails** | Incorrect credentials or path | Verify `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, and `FTP_REMOTE_DIR` secrets on GitHub. |
| **Database Connection Refused** | Incorrect `DB_HOST` | Some shared hosts require `localhost` instead of `127.0.0.1`. |
