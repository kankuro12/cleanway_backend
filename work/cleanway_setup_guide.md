# CleanWay Backend — Production Setup & MariaDB Guide

> **Target Environment**: Ubuntu 22.04 LTS / 24.04 LTS  
> **Tech Stack**: Nginx 1.24+, PHP 8.4-FPM, MariaDB 10.11+, Redis 7+, Fail2Ban Guard, Supervisor Queue Worker, Cron Scheduler  
> **HTML Interactive Guide**: [`docs/cleanway_setup_guide.html`](file:///e:/laravel%20pojects/nz/app/cleanway_backend/docs/cleanway_setup_guide.html)

---

## 1. System Requirements & Packages

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git unzip software-properties-common ufw supervisor fail2ban nginx
```

---

## 2. PHP 8.4-FPM Installation

```bash
# Add Ondřej PPA and fix 404 for Ubuntu Resolute by mapping to Noble (24.04 LTS)
sudo add-apt-repository ppa:ondrej/php -y
sudo sed -i 's/resolute/noble/g' /etc/apt/sources.list.d/ondrej-ubuntu-php*.sources 2>/dev/null || true
sudo sed -i 's/resolute/noble/g' /etc/apt/sources.list.d/ondrej-ubuntu-php*.list 2>/dev/null || true
sudo apt update

# Install PHP 8.4-FPM & Extensions
sudo apt install -y php8.4-fpm php8.4-cli php8.4-common php8.4-mysql \
  php8.4-mbstring php8.4-xml php8.4-curl php8.4-gd php8.4-zip \
  php8.4-redis php8.4-intl php8.4-bcmath php8.4-opcache
```

---

## 3. MariaDB & Redis Setup

### MariaDB Provisioning
```bash
sudo apt install -y mariadb-server mariadb-client
sudo systemctl enable --now mariadb
sudo mariadb-secure-installation
```

```sql
sudo mariadb

CREATE DATABASE cleanway_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cleanway_user'@'localhost' IDENTIFIED BY 'StrongSecretPassword123!';
GRANT ALL PRIVILEGES ON cleanway_db.* TO 'cleanway_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Redis Setup
```bash
sudo apt install -y redis-server
sudo systemctl enable --now redis-server
redis-cli ping # Expected: PONG
```

---

## 4. Codebase & Permissions (MariaDB Driver)

### A. Upgrade Composer for PHP 8.4 (Fix E_STRICT Deprecation)
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
composer --version
```

### B. Clone & Install Dependencies
```bash
sudo mkdir -p /var/www/cleanway_backend
sudo chown -R $USER:$USER /var/www/cleanway_backend
cd /var/www/cleanway_backend
git clone https://github.com/your-org/cleanway_backend.git .

composer install --no-dev --optimize-autoloader
cp .env.example .env
```

### `.env` File (MariaDB High-Performance Unix Socket Connection)
```ini
APP_NAME="CleanWay Backend"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cleanway.needtechnosoft.com

DB_CONNECTION=mariadb
DB_SOCKET=/var/run/mysqld/mysqld.sock
DB_DATABASE=cleanway_db
DB_USERNAME=cleanway_user
DB_PASSWORD="StrongSecretPassword123!"

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

sudo chown -R www-data:www-data /var/www/cleanway_backend/storage /var/www/cleanway_backend/bootstrap/cache
sudo chmod -R 775 /var/www/cleanway_backend/storage /var/www/cleanway_backend/bootstrap/cache
```

---

## 5. Nginx Server Installation & Virtual Host

### A. Install & Enable Nginx
```bash
sudo apt install -y nginx
sudo systemctl enable --now nginx
```

### B. Create Virtual Host Configuration (`/etc/nginx/sites-available/cleanway.needtechnosoft.com.conf`)

```nginx
# Cloudflare Real IP Restoration
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
set_real_ip_from 2400:cb00::/32;
set_real_ip_from 2606:4700::/32;
set_real_ip_from 2803:f800::/32;
set_real_ip_from 2405:b500::/32;
set_real_ip_from 2405:8100::/32;
set_real_ip_from 2500:9000::/32;
set_real_ip_from 2c0f:f248::/32;

real_ip_header CF-Connecting-IP;
real_ip_recursive on;

server {
    listen 80;
    listen [::]:80;
    server_name cleanway.needtechnosoft.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name cleanway.needtechnosoft.com;
    root /var/www/cleanway_backend/public;

    # Cloudflare Origin CA Certificate (Valid up to 15 years)
    ssl_certificate /etc/ssl/certs/cleanway-origin.pem;
    ssl_certificate_key /etc/ssl/private/cleanway-origin.key;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    index index.php;
    charset utf-8;
    client_max_body_size 25M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\. {
        deny all;
    }
}
```

```bash
sudo ln -sf /etc/nginx/sites-available/cleanway.needtechnosoft.com.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### C. Cloudflare Origin CA Certificate Installation

Since Cloudflare proxy handles public TLS termination, install a 15-year Cloudflare Origin CA Certificate on your server:

1. **Generate Certificate in Cloudflare**:
   - Go to **Cloudflare Dashboard** -> **SSL/TLS** -> **Origin Server** -> **Create Certificate**.
   - Select **RSA (2048)** or **ECDSA**, validity **15 years**, hostnames `cleanway.needtechnosoft.com`.
2. **Save Certificates on Origin Server**:
   - Paste **Origin Certificate** into `/etc/ssl/certs/cleanway-origin.pem` (`chmod 644`).
   - Paste **Private Key** into `/etc/ssl/private/cleanway-origin.key` (`chmod 600`).
3. **Set Cloudflare Encryption Mode**:
   - Set Cloudflare SSL/TLS encryption mode to **Full (strict)**.

# 3. Create Nginx reload hook on successful renewal
sudo mkdir -p /etc/letsencrypt/renewal-hooks/deploy
sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh > /dev/null << 'EOF'
#!/usr/bin/env bash
systemctl reload nginx
EOF
sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh

# 4. Enable Systemd Renewal Timer (runs twice daily)
sudo systemctl enable --now certbot.timer

# 5. Add Daily Cron Fallback (/etc/cron.d/cleanway-certbot-renew)
echo "30 3 * * * root certbot renew --quiet --deploy-hook 'systemctl reload nginx'" | sudo tee /etc/cron.d/cleanway-certbot-renew

# 6. Test Auto-Renewal (Dry Run)
sudo certbot renew --dry-run
```

---

## 6. Fail2Ban Security Configuration

### `/etc/fail2ban/jail.local`
```ini
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5
banaction = iptables-multiport

[sshd]
enabled = true
port    = ssh

[nginx-http-auth]
enabled  = true
filter   = nginx-http-auth
port     = http,https
logpath  = /var/log/nginx/error.log

[laravel-login]
enabled  = true
filter   = laravel-login
port     = http,https
logpath  = /var/log/nginx/access.log
maxretry = 5
findtime = 5m
bantime  = 2h
```

### Laravel Login Filter (`/etc/fail2ban/filter.d/laravel-login.conf`)
```ini
[Definition]
failregex = ^<HOST> .* "POST /login HTTP/.*" (401|422|429)
ignoreregex =
```

Enable Fail2Ban:
```bash
sudo systemctl enable --now fail2ban
sudo systemctl restart fail2ban
sudo fail2ban-client status
```

---

## 7. Supervisor Worker Configuration

Create `/etc/supervisor/conf.d/cleanway-worker.conf`:

```ini
[program:cleanway-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cleanway_backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/cleanway_backend/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cleanway-worker:*
```

---

## 8. Cron Scheduler Entry

```bash
sudo crontab -u www-data -e
```

Add:
```cron
* * * * * cd /var/www/cleanway_backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Production Caching

```bash
cd /var/www/cleanway_backend
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

