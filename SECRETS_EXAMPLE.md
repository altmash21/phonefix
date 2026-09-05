# 🔐 GitHub Secrets Reference (SSH Deployment)

URL to configure secrets:
👉 **[https://github.com/altmash21/mm/settings/secrets/actions](https://github.com/altmash21/mm/settings/secrets/actions)**

Click **New repository secret** and add these:

---

## 1. `SSH_HOST`
- **Name:** `SSH_HOST`
- **Example:** `yourdomain.com` or server IP `123.45.67.89`

---

## 2. `SSH_USER`
- **Name:** `SSH_USER`
- **Example:** Your DirectAdmin login username (e.g. `u1234567` or `admin_user`)

---

## 3. `SSH_PORT`
- **Name:** `SSH_PORT`
- **Example:** `22` *(or whatever port YouStable specifies)*

---

## 4. `SSH_PRIVATE_KEY`
- **Name:** `SSH_PRIVATE_KEY`
- **Value:** Paste your generated private key:
```text
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAABFwAAAAdzc2gtcn
...
...many lines...
...
-----END OPENSSH PRIVATE KEY-----
```

---

## 5. `SSH_TARGET_DIR` (Optional)
- **Name:** `SSH_TARGET_DIR`
- **Default:** `./`
- **Example for DirectAdmin:** `./` or `domains/yourdomain.com/`
*(Rsync will sync the staged `public_html/` and `app/` folders into this directory).*
