---
description: Development workflow for deploying the Drupal 11 IBEW site to a WHM/cPanel server via GitHub Actions
---

# IBEW Drupal 11 — Development & Deployment Workflow

## Architecture Overview

```
┌──────────────┐     git push      ┌──────────────┐    rsync + SSH    ┌──────────────────┐
│  Local Dev   │ ───────────────► │   GitHub     │ ────────────────► │  cPanel Server   │
│  (DDEV)      │                  │   (main)     │   GitHub Actions  │  (Production)    │
└──────────────┘                  └──────────────┘                   └──────────────────┘
```

- **Local Dev**: DDEV on your machine (Windows)
- **GitHub**: Source of truth for code
- **Production**: WHM/cPanel server, auto-deployed on push to `main`

---

## 1. Local Development (Daily Work)

### Start your environment
```bash
ddev start
```

### Make code changes
Edit files in your IDE. Key directories:
- `web/themes/custom/ibew_theme/` — Your custom theme
- `web/modules/custom/` — Your custom modules
- `composer.json` — Add/remove Drupal modules

### Add a new Drupal module
```bash
ddev composer require drupal/module_name
ddev drush en module_name
```

### View the site locally
```
https://ibew-drupal.ddev.site
```

---

## 2. Configuration Management

### After making config changes in the Drupal admin UI:
```bash
# Export config to files
ddev drush config:export -y

# Check what changed
git diff config/

# Commit the config changes
git add config/
git commit -m "Update config: describe what you changed"
```

### Config is auto-imported on deploy (see GitHub Actions workflow)

---

## 3. Deploying to Production

### Standard deploy (automatic):
```bash
# Stage your changes
git add .

# Commit
git commit -m "Your descriptive commit message"

# Push to GitHub — this triggers auto-deploy
git push origin main
```

GitHub Actions will automatically:
1. ✅ rsync your code to the cPanel server
2. ✅ Run `composer install --no-dev`
3. ✅ Run `drush cr` (clear cache)
4. ✅ Run `drush updatedb` (database updates)
5. ✅ Run `drush config:import` (apply config changes)

### Manual deploy (if needed):
You can also trigger a deploy manually from the GitHub Actions tab.

---

## 4. Database Management

### Export local DB (for backup or migration):
```bash
ddev export-db --gzip --file=backups/db_backup.sql.gz
```

### Import a DB dump locally:
```bash
ddev import-db --file=backups/db_backup.sql.gz
```

### Import DB on production (via SSH):
```bash
ssh user@server "cd /path/to/site && gunzip < backup.sql.gz | vendor/bin/drush sql:cli"
```

---

## 5. Initial Server Setup (One-Time)

### On your cPanel server:

1. **Enable SSH access** in cPanel → Terminal or via WHM
2. **Install Composer** (if not already):
   ```bash
   cd ~
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php --install-dir=$HOME/bin --filename=composer
   echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
   source ~/.bashrc
   ```
3. **Create MySQL database** via cPanel → MySQL Databases
4. **Create `.env` file** on the server (copy from `.env.example`):
   ```bash
   cd /home/username/public_html
   cp .env.example .env
   nano .env  # Fill in your DB credentials
   ```
5. **Create private files directory**:
   ```bash
   mkdir -p /home/username/private
   ```
6. **Set file permissions**:
   ```bash
   chmod 755 web/sites/default
   chmod 644 web/sites/default/settings.php
   mkdir -p web/sites/default/files
   chmod 775 web/sites/default/files
   ```

### On GitHub:

1. Go to **Settings → Secrets and variables → Actions**
2. Add these secrets:
   | Secret Name   | Value                                    |
   |---------------|------------------------------------------|
   | `SSH_HOST`    | Your server IP or hostname               |
   | `SSH_USER`    | Your cPanel username                     |
   | `SSH_KEY`     | Your SSH private key (full contents)     |
   | `SSH_PORT`    | `22` (or your custom SSH port)           |
   | `DEPLOY_PATH` | `/home/username/public_html`            |

3. **Generate an SSH key pair** for deployment:
   ```bash
   ssh-keygen -t ed25519 -C "github-deploy" -f deploy_key -N ""
   ```
   - Add `deploy_key.pub` to the server's `~/.ssh/authorized_keys`
   - Paste contents of `deploy_key` into the `SSH_KEY` GitHub secret

---

## 6. File Structure

```
ibew-theme-design/
├── .env.example              # Environment variable template
├── .github/workflows/
│   └── deploy.yml            # GitHub Actions auto-deploy
├── .gitignore                # Comprehensive ignore rules
├── composer.json             # PHP dependencies
├── composer.lock             # Locked dependency versions
├── config/sync/              # Drupal config export (version controlled)
├── scripts/
│   ├── db-export.sh          # Database export helper
│   └── config-export.sh      # Config export helper
├── web/
│   ├── sites/default/
│   │   ├── settings.php            # Main settings (env detection)
│   │   ├── settings.production.php # Production DB + settings
│   │   ├── settings.local.php      # Local overrides (not in git)
│   │   └── settings.ddev.php       # DDEV auto-generated (not in git)
│   ├── modules/custom/            # YOUR custom modules (in git)
│   ├── themes/custom/ibew_theme/  # YOUR custom theme (in git)
│   ├── core/                      # Drupal core (NOT in git, composer install)
│   ├── modules/contrib/           # Contrib modules (NOT in git, composer install)
│   └── themes/contrib/            # Contrib themes (NOT in git, composer install)
└── vendor/                        # PHP deps (NOT in git, composer install)
```

---

## 7. Branching Strategy (Optional)

For a simple workflow:
- `main` = production (auto-deploys)
- Feature branches for larger changes → merge to `main` via Pull Request

---

## Quick Reference

| Task                        | Command                                          |
|-----------------------------|--------------------------------------------------|
| Start local dev             | `ddev start`                                     |
| View local site             | `ddev launch`                                    |
| Add module                  | `ddev composer require drupal/module_name`       |
| Enable module               | `ddev drush en module_name`                      |
| Export config               | `ddev drush config:export -y`                    |
| Clear cache (local)         | `ddev drush cr`                                  |
| Deploy to production        | `git push origin main`                           |
| Export DB backup             | `ddev export-db --gzip --file=backups/db.sql.gz`|
| SSH to server               | `ssh user@server`                                |
| Clear cache (production)    | `ssh ... "cd /path && vendor/bin/drush cr"`      |
