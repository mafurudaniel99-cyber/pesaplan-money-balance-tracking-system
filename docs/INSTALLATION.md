# Installation Guide

This guide walks through installing PesaPlan locally using **XAMPP** on Windows (the same steps apply to Linux `/opt/lampp` and macOS `/Applications/XAMPP`).

## Prerequisites

- XAMPP 8.2 or newer (includes Apache 2.4, PHP 8.2+, MariaDB 10.4+)
- A modern browser (Chrome, Firefox, Edge, Safari)
- ~50 MB free disk space

## Step 1 — Copy the project

Copy the entire `pesa-plan/` folder into your XAMPP htdocs directory:

- **Windows**: `C:\xampp\htdocs\pesa-plan\`
- **Linux**: `/opt/lampp/htdocs/pesa-plan/`
- **macOS**: `/Applications/XAMPP/htdocs/pesa-plan/`

The project root must contain `public/`, `app/`, `config/`, `database/`, etc.

## Step 2 — Start Apache and MySQL

Open the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**. Both should turn green.

## Step 3 — Create the database

1. Open `http://localhost/phpmyadmin` in your browser.
2. Click **Databases** → **Create database**.
3. Database name: `pesaplan`
4. Collation: `utf8mb4_unicode_ci`
5. Click **Create**.

## Step 4 — Import the schema and seed data

1. In phpMyAdmin, select the `pesaplan` database from the left sidebar.
2. Click the **Import** tab.
3. Choose file → `database/schema.sql` → **Go**.
4. Repeat for `database/seed.sql`.

You should now see ~17 tables (`users`, `transactions`, `budgets`, etc.) and 17 default category rows in `expense_categories`.

## Step 5 — Configure database credentials

1. Open the `config/` folder.
2. Copy `database.example.php` → `database.php`.
3. Edit `database.php`:

```php
return [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'name'    => 'pesaplan',
    'user'    => 'root',       // default XAMPP user
    'pass'    => '',           // default XAMPP has no password
    'charset' => 'utf8mb4',
    'timezone'=> '+03:00',
];
```

For production, **never** use `root` with no password — see [DEPLOYMENT.md](DEPLOYMENT.md).

## Step 6 — Open the application

Visit:

```
http://localhost/pesa-plan/public/
```

You should see the PesaPlan landing page. Click **Anza bure** to register your first account.

## Step 7 — Onboarding

After registering, the onboarding wizard appears:

1. Enter your expected monthly salary (TZS) and the day you usually receive it.
2. (Optional) List recurring essential expenses — one per line, format: `Name | amount` (e.g., `Kodi ya nyumba | 150000`).
3. (Optional) List outstanding debts — format: `Name | balance | minimum payment`.
4. (Optional) Set a starter savings target.
5. Click **Maliza usanidi na anza**.

You will land on the dashboard with your data already populated.

## Optional — Use a virtual host (cleaner URL)

For `http://pesaplan.local/` instead of the long path:

1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` (Windows).
2. Add:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/pesa-plan/public"
    ServerName pesaplan.local
    <Directory "C:/xampp/htdocs/pesa-plan/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
3. Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator) and add:
```
127.0.0.1   pesaplan.local
```
4. Restart Apache.
5. Visit `http://pesaplan.local/`.

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|-------------|-----|
| Blank page | PHP error display off | Check `storage/logs/` or set `debug => true` in `config/app.php` |
| "Faili ya usanidi wa hifadhidata haipo" | `config/database.php` missing | Copy from `database.example.php` |
| 404 on every link | `mod_rewrite` not enabled | In `httpd.conf`, uncomment `LoadModule rewrite_module` |
| "Access denied for user" | Wrong DB credentials | Re-check `config/database.php` |
| Buttons do nothing | JS not loaded | Check browser console; ensure `/assets/js/app.js` is reachable |
| Charts don't render | JS error | Check browser console; ensure `/assets/js/charts.js` is loaded |

## Uninstall

1. Delete the project folder from `htdocs/`.
2. In phpMyAdmin, drop the `pesaplan` database.
