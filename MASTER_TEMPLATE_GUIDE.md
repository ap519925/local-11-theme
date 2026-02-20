# IBEW Drupal 11 - Master Template Deployment Guide

This repository is configured to serve as a **Master Template** for spinning up new IBEW Local websites. By cloning this codebase, importing the starter database, and adjusting a few settings, you can deploy a fully functional, identically-featured site for any other domain (e.g., Local 11, Local 42, etc.).

## Prerequisite: The Starter Export
Before deploying a new site, you should have the latest **Database Dump** (`.sql` or `.sql.gz`) and the **Files Directory** (`web/sites/default/files/`) exported from this master setup. 

The typical deployment flow for a new site:
1. Copy the codebase (this repository).
2. Create `.env` from `.env.example` and set up credentials.
3. Import the starter Database Dump so that all content, settings, and structures are loaded.
4. Copy the `web/sites/default/files/` directory, so all images attached to the master content are present.
5. In the Drupal UI, change the specific details (Site Name, Theme Colors, etc.) to match the new Local.

---

## Step-by-Step Deployment Instructions

### 1. File Upload & Installation
Upload the contents of this repository to your new server (`public_html` or equivalent).
If you deploy via Git, clone the repo directly to the webroot. Otherwise, upload everything except `.git`, `node_modules` and local development files. 
You **MUST** connect via SSH to the server and install Composer dependencies, as they are not tracked in Git.

```bash
cd /home/YOUR_CPANEL_USER/public_html
composer install --no-dev --optimize-autoloader
```

### 2. Set Up the Environment
Create the `.env` file in your root folder (the same directory that contains the `web/` folder). Note that `.env` is typically one level *above* `web/`.
Copy the contents from `.env.example` to start:

```bash
cp .env.example .env
nano .env
```

You must update the following variables with the credentials for your **new** server database and production domain:
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_HOST` (Usually `localhost`)
- `DRUPAL_HASH_SALT` (Generate a new 64-character string)
- `TRUSTED_HOSTS` (Set this to `^newdomain\.com$,^www\.newdomain\.com$`)
- `GOOGLE_MAPS_API_KEY`

### 3. Restore Starter Database & Files
You need the database and standard files exported from your fully-built Master site.
1. Use PHPMyAdmin, cPanel, or `drush sql-cli` to import the `.sql` starter database dump into the newly created database.
2. Upload the `web/sites/default/files/` directory via FTP or SSH/rsync into your new codebase. 

```bash
# Set proper permissions so Drupal can upload files and cache CSS/JS
chmod 755 web/sites/default
chmod 644 web/sites/default/settings.php
chmod 644 web/sites/default/settings.production.php
mkdir -p web/sites/default/files
chmod -R 775 web/sites/default/files
```

### 4. Clear Caches & Sync Settings
Since this is a cloned site, you need to clear caches to ensure there are no remnants of the old domain.

```bash
vendor/bin/drush cr
```

If you have made code-level configuration changes in the master template (like adding fields), run the config import:
```bash
vendor/bin/drush config:import -y
vendor/bin/drush cr
```

---

## Post-Deployment Personalization (Theming)
This template theme is designed to be fully customizable via the UI.

Log in to the new Drupal admin dashboard with the admin credentials from your database.

1. **Site Information:**
   - Go to `Configuration > System > Basic site settings` (`/admin/config/system/site-information`)
   - Update **Site name**, **Slogan**, and **Contact Email**.

2. **Theme Appearance (Colors and Logo):**
   - Go to `Appearance > Settings > IBEW Theme` (`/admin/appearance/settings/ibew_theme`)
   - Uncheck "Use the logo supplied by the theme" and upload the **new Local's Logo**.
   - Review and update the **Light/Dark Mode Color Settings** to match the new union's branding. 

3. **Homepage Layout/Blocks:**
   - Update `Structure > Block layout` to adjust any union-specific text blocks (e.g., the Footer Contact info, Social Links).
   - If using the Layout Builder for the homepage, simply go to the Homepage and click **Layout** to swap out images and specific textual sections.

4. **Change Map Configurations**:
   - Go to `Configuration > IBEW Contractor Map Settings` (`/admin/config/ibew/contractor-map`)
   - Change the `default_lat` and `default_lng` for the map's starting location to center on your new local's geographical area.
   - Delete/edit old Contractor Profiles to match the new Local.
