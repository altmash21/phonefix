# 🔐 Example GitHub Secrets Reference

This file shows realistic dummy examples of what to fill in on GitHub under:
**Repository Settings** → **Secrets and variables** → **Actions** → **New repository secret**

URL: `https://github.com/altmash21/mm/settings/secrets/actions`

---

## 1. `FTP_SERVER`

- **Secret Name:** `FTP_SERVER`
- **Example Value:**
```text
ftp.mobitrack.shop
```
*(Alternative example if using server IP: `198.51.100.45`)*

---

## 2. `FTP_USERNAME`

- **Secret Name:** `FTP_USERNAME`
- **Example Value:**
```text
deployer@mobitrack.shop
```
*(Or your cPanel username, e.g.: `cpuser` or `u987654321`)*

---

## 3. `FTP_PASSWORD`

- **Secret Name:** `FTP_PASSWORD`
- **Example Value:**
```text
mY$tr0ngFtpP@ssw0rd!2026
```

---

## 4. `FTP_REMOTE_DIR` (Optional)

- **Secret Name:** `FTP_REMOTE_DIR`
- **Example Value:**
```text
public_html/
```
*(Or `./` if your FTP user logs in directly inside the web folder)*

---

## 📋 Copy-Paste Cheat Sheet

If you keep your own private notes locally on your computer, you can store your credentials in this format:

```text
========================================
MOBITRACK PRODUCTION HOSTING CREDENTIALS
========================================
Domain:       https://mobitrack.shop
Control Panel: https://mobitrack.shop:2083
FTP Host:     ftp.mobitrack.shop
FTP User:     deployer@mobitrack.shop
FTP Pass:     mY$tr0ngFtpP@ssw0rd!2026
FTP Folder:   public_html/

Database:     cpuser_mobitrack
DB User:      cpuser_admin
DB Pass:      DbSecur3P@ssword#2026
DB Host:      localhost
========================================
```

> ⚠️ **IMPORTANT NOTE:** Never commit your real passwords to GitHub! This example file contains only placeholder text for reference.
