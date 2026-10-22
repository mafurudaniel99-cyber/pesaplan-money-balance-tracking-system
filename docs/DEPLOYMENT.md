# Deployment Guide

This guide covers production deployment of PesaPlan on a Linux server with Apache, MySQL 8, and PHP 8.2+.

## 1. Server Requirements

- Ubuntu 22.04 LTS or Debian 12 (recommended)
- PHP 8.2 or newer with extensions: `mysqli`, `mbstring`, `openssl`, `curl`
- MySQL 8.0 or MariaDB 10.6+
- Apache 2.4 with `mod_rewrite`, `mod_headers`, `mod_ssl`
- 1 GB RAM minimum (2 GB recommended)
- 10 GB disk minimum

## 2. Install Dependencies

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php8.2 php8.2-mysql php8.2-mbstring php8.2-curl php8.2-xml
sudo a2enmod rewrite headers ssl
sudo systemctl restart apache2
```

## 3. Create Database and User

```bash
sudo mysql
```

```sql
CREATE DATABASE pesaplan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pesaplan'@'localhost' IDENTIFIED BY 'STRONG_RANDOM_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, DROP, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES
  ON pesaplan.* TO 'pesaplan'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

The `pesaplan` user has only the privileges the application needs — no `GRANT`, no `SUPER`, no access to other databases.

## 4. Import Schema

```bash
cd /var/www/pesa-plan
mysql -u pesaplan -p pesaplan < database/schema.sql
mysql -u pesaplan -p pesaplan < database/seed.sql
```

## 5. Deploy Code

```bash
sudo mkdir -p /var/www/pesa-plan
sudo chown -R $USER:$USER /var/www/pesa-plan
# Copy or git-clone the project here
# Set permissions
sudo chown -R www-data:www-data /var/www/pesa-plan/storage
sudo chmod -R 775 /var/www/pesa-plan/storage
```

## 6. Configure

Copy and edit configuration:

```bash
cp config/database.example.php config/database.php
nano config/database.php
```

Set production credentials:
```php
return [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'name'    => 'pesaplan',
    'user'    => 'pesaplan',
    'pass'    => 'STRONG_RANDOM_PASSWORD',
    'charset' => 'utf8mb4',
    'timezone'=> '+03:00',
];
```

Edit `config/app.php`:
```php
'env'   => 'production',
'debug' => false,
'session_secure' => true,  // HTTPS only
```

## 7. Apache Virtual Host

Create `/etc/apache2/sites-available/pesa-plan.conf`:

```apache
<VirtualHost *:80>
    ServerName pesaplan.example.com
    Redirect permanent / https://pesaplan.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName pesaplan.example.com
    DocumentRoot /var/www/pesa-plan/public

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/pesa-plan.example.com/cert.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/pesa-plan.example.com/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/pesa-plan.example.com/chain.pem

    <Directory /var/www/pesa-plan/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;"

    # Deny access to sensitive paths (defense in depth)
    <Directory /var/www/pesa-plan/config>
        Require all denied
    </Directory>
    <Directory /var/www/pesa-plan/storage>
        Require all denied
    </Directory>
    <Directory /var/www/pesa-plan/app>
        Require all denied
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/pesaplan_error.log
    CustomLog ${APACHE_LOG_DIR}/pesaplan_access.log combined
</VirtualHost>
```

Enable:
```bash
sudo a2ensite pesa-plan
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## 8. HTTPS via Let's Encrypt

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d pesaplan.example.com
```

Certbot will fetch and install the certificate and set up auto-renewal.

## 9. Backups

Create `/etc/cron.daily/pesa-plan-backup`:

```bash
#!/bin/bash
set -e
BACKUP_DIR=/var/backups/pesa-plan
mkdir -p $BACKUP_DIR
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump --single-transaction -u pesaplan -p'STRONG_RANDOM_PASSWORD' pesaplan | gzip > $BACKUP_DIR/pesa-plan_$DATE.sql.gz
find $BACKUP_DIR -name "pesa-plan_*.sql.gz" -mtime +30 -delete
```

```bash
sudo chmod +x /etc/cron.daily/pesa-plan-backup
```

For off-site backup, sync to S3 or rsync to another server.

## 10. Logging

PHP errors go to `/var/log/apache2/pesa-plan_error.log`. Rotate with `logrotate`:

Create `/etc/logrotate.d/pesa-plan`:
```
/var/log/apache2/pesa-plan_*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 640 www-data adm
}
```

## 11. Performance

- Enable PHP OPcache: `sudo apt install php8.2-opcache` (default config is fine).
- MySQL query cache is removed in MySQL 8; rely on the indexes documented in [DATABASE.md](DATABASE.md).
- For higher traffic, consider PHP-FPM instead of mod_php.

## 12. Upgrading

```bash
cd /var/www/pesa-plan
git pull origin main
# Apply any new migrations
mysql -u pesaplan -p pesaplan < database/migrations/YYYYMMDD_xxx.sql
# Clear storage caches
rm -rf storage/logs/*.log storage/exports/*
sudo systemctl reload apache2
```

Always test the upgrade on a staging copy first.

## 13. Security Checklist

- [ ] Database user `pesaplan` has only DML/DLL on `pesaplan.*`, no SUPER
- [ ] `config/database.php` is owned by `root:www-data` with mode `0640`
- [ ] `config/app.php` has `debug => false`
- [ ] `config/app.php` has `session_secure => true`
- [ ] HTTPS is enforced (port 80 redirects to 443)
- [ ] HSTS header is set (max-age 1 year)
- [ ] CSP header is set
- [ ] `storage/` and `config/` are not web-accessible
- [ ] Backups run daily and are tested by restoring to a staging server
- [ ] Apache access logs are rotated
- [ ] `mod_security` with OWASP CRS is enabled (optional but recommended)
- [ ] SSH key-only login (no password auth)
- [ ] UFW firewall: only 22, 80, 443 open
- [ ] Fail2ban on SSH and Apache

## 14. Monitoring

- Monitor `/var/log/apache2/pesa-plan_error.log` for PHP errors.
- Monitor MySQL slow query log: `SET GLOBAL slow_query_log = 'ON'; SET GLOBAL long_query_time = 2;`
- Set up Uptime Robot or similar to ping `https://pesaplan.example.com/login` every 5 minutes.
- Monitor disk usage — `storage/exports/` can grow if many CSV exports are generated.

## 15. Rollback

If a deployment breaks:
```bash
cd /var/www/pesa-plan
git checkout <previous-commit>
mysql -u pesaplan -p pesaplan < /var/backups/pesa-plan/<yesterday>.sql.gz
sudo systemctl reload apache2
```
