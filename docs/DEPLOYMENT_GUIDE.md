# Panduan Deployment Produksi — CloudCampus Storage
Target Server: Single VPS (Ubuntu 22.04 / 24.04 LTS), Nginx, PHP 8.3-FPM, Supervisor, SSL Let's Encrypt.

---

## 1. Persiapan Server VPS
Jalankan update paket sistem dan install runtime:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip sqlite3

# Tambah repositori PHP Ondrej
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 & ekstensi yang dibutuhkan
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-gd php8.3-sqlite3 php8.3-mysql php8.3-bcmath \
    php8.3-opcache nginx supervisor
```

---

## 2. Kloning Repositori & Hak Akses Direktori
```bash
sudo mkdir -p /var/www/cloudcampus
sudo chown -R $USER:www-data /var/www/cloudcampus

git clone <REPO_URL> /var/www/cloudcampus
cd /var/www/cloudcampus

# Set izin direktori storage & bootstrap cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 3. Konfigurasi Lingkungan (`.env`) & Kompilasi Aset
```bash
cp .env.example .env
nano .env
```
Pastikan pengaturan berikut terisi di `.env`:
```ini
APP_NAME="CloudCampus Storage"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cloud.kampus.ac.id

# Konfigurasi Storage & Kuota
DEFAULT_QUOTA_BYTES=5368709120
MAX_UPLOAD_SIZE=102400
FILESYSTEM_DISK=local

# Queue Connection
QUEUE_CONNECTION=database
```

Jalankan instalasi dependensi & build aset:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
npm ci && npm run build

# Cache optimasi Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Konfigurasi Nginx & PHP-FPM
Salin konfigurasi vhost Nginx dan PHP:
```bash
# Nginx
sudo cp deploy/nginx/cloudcampus.conf /etc/nginx/sites-available/cloudcampus
sudo ln -s /etc/nginx/sites-available/cloudcampus /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# PHP-FPM Custom Settings
sudo cp deploy/php/cloudcampus.ini /etc/php/8.3/fpm/conf.d/99-cloudcampus.ini
sudo systemctl restart php8.3-fpm
```

---

## 5. Konfigurasi Supervisor (Background Queue Worker)
Supervisor bertugas menjalankan queue worker Laravel di latar belakang secara terus menerus:
```bash
sudo cp deploy/supervisor/cloudcampus-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cloudcampus-worker:*
```

---

## 6. Pemasangan SSL (Let's Encrypt) & Setup Backup Otomatis
Jalankan skrip SSL:
```bash
chmod +x deploy/scripts/setup-ssl.sh deploy/scripts/backup.sh
sudo ./deploy/scripts/setup-ssl.sh cloud.kampus.ac.id admin@kampus.ac.id
```

Pasang crontab untuk pemeliharaan otomatis:
```bash
crontab deploy/crontab.txt
```

---

## 7. Checklist Verifikasi Smoke Test Sebelum Go-Live
1. [x] Akses HTTPS root `https://cloud.kampus.ac.id` memuat landing page.
2. [x] Endpoint status sistem `/up` merespons status `200 OK`.
3. [x] Login SSO dan login publik berfungsi tanpa hambatan.
4. [x] Pengunggahan file 50MB+ berhasil dan progress bar akurat.
5. [x] Unduh folder sebagai `.zip` terproses oleh background queue worker.
6. [x] Direktori `.env` dan `storage/app/` tidak dapat diakses langsung via browser.
