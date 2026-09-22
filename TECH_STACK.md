# Tech Stack — CloudCampus Storage

Disusun berdasarkan: Sistem Akademik existing berbasis **PHP/Laravel**, penyimpanan file di **server sendiri (VPS)**, skala awal **kecil (<1000 user)**, dan kebutuhan **SSO + akses publik**.

## 1. Backend
| Komponen | Pilihan | Alasan |
|---|---|---|
| Framework | **Laravel 11.x** | Selaras dengan Sistem Akademik, ekosistem paket lengkap (Sanctum, Filesystem, Queue) |
| Bahasa | PHP 8.3 | Versi stabil terbaru yang didukung Laravel 11 |
| Database | **MySQL 8 / MariaDB 10.11** | Umum dipakai di hosting kampus, kompatibel dengan sistem akademik existing |
| Auth API/Token | **Laravel Sanctum** | Ringan, cocok untuk SPA + mobile + token API antar sistem |
| SSO Bridge | Custom OAuth2/JWT client (atau **Laravel Socialite** dengan custom provider) | Menyesuaikan sistem akademik existing sebagai Identity Provider |
| Queue | Laravel Queue (driver `database`, upgrade ke Redis jika load naik) | Untuk proses async: generate thumbnail, zip folder besar, kirim email |
| Storage Abstraction | **Laravel Filesystem (Flysystem)**, disk `local` | Struktur folder per user, mudah pindah ke `s3`/MinIO nanti tanpa ubah kode bisnis |
| File Utilities | `spatie/laravel-medialibrary` (opsional) atau custom model `File`/`Folder` | Manajemen metadata file, thumbnail, kategori |
| Permission/Roles | `spatie/laravel-permission` | Role: super-admin, admin-kampus, user |
| Image Processing | `intervention/image` | Generate thumbnail untuk preview gambar |
| Zip Folder Download | `ZipArchive` (native PHP) atau `chumper/zipper` | Download folder sebagai .zip |
| Testing | **Pest PHP** (di atas PHPUnit) | Sintaks ringkas, umum dipakai proyek Laravel modern |
| API Docs | Laravel + `scramble` atau Postman collection manual | Dokumentasi endpoint untuk konsumsi Sistem Akademik |

## 2. Frontend
Karena tim vibe-coding dengan AI Agent dan ingin *satu stack* yang mudah dikelola, dua opsi:

### Opsi A (Direkomendasikan untuk MVP): Laravel + Livewire + Alpine.js + Tailwind CSS
- Tidak perlu build SPA terpisah, satu repo, satu bahasa (PHP + sedikit JS).
- Livewire cocok untuk interaksi file explorer (upload, rename, drag-drop dengan bantuan Alpine.js untuk state UI ringan).
- Tailwind CSS untuk styling cepat sesuai style guide di UIUX_BRIEF.md.

### Opsi B (Jika ingin UI lebih kaya/SPA): Laravel API + Vue 3 (atau React) + Tailwind CSS
- Backend Laravel murni sebagai REST API.
- Frontend terpisah (Vite + Vue/React), cocok bila nanti ingin bikin mobile app dengan API yang sama.
- Kompleksitas lebih tinggi — lebih cocok kalau tim sudah terbiasa SPA.

> **Rekomendasi**: mulai dari **Opsi A** untuk kecepatan development (skala kecil, satu VPS), dengan desain backend tetap API-first (lihat §4) supaya bisa "upgrade" ke Opsi B atau dikonsumsi Sistem Akademik tanpa refactor besar.

## 3. Infrastruktur
| Komponen | Pilihan |
|---|---|
| Web Server | Nginx + PHP-FPM |
| OS | Ubuntu 22.04/24.04 LTS |
| Process Manager Queue | Supervisor (menjaga `queue:work` tetap jalan) |
| SSL | Let's Encrypt (Certbot) |
| Backup | Cron job dump database harian + rsync folder storage ke lokasi terpisah |
| Monitoring Disk | Script cron sederhana / Laravel command cek kapasitas disk & kirim alert |
| Deployment | Git pull + `composer install` + `php artisan migrate` (manual atau via script `deploy.sh`); opsional CI/CD sederhana (GitHub Actions) saat sudah stabil |

## 4. Prinsip Desain Backend (API-first)
- Seluruh aksi file/folder (upload, list, delete, share) diimplementasikan sebagai **Service Class**, dipanggil baik dari Controller web (Livewire) maupun API Controller.
- Endpoint REST versi `/api/v1/...` diamankan token Sanctum — dipakai oleh Sistem Akademik untuk integrasi programatik.
- Struktur direktori fisik file: `storage/app/users/{user_id}/{folder_path}/{filename}` — nama file asli disimpan di kolom database, nama file di disk di-hash untuk menghindari collision & masalah karakter aneh.

## 5. Struktur Autentikasi Ganda
- Tabel `users` punya kolom `account_type` (`academic` / `public`) dan `external_id` (NIM/NIP dari Sistem Akademik, nullable untuk publik).
- Guard `web` standar Laravel untuk publik (email+password, ada `email_verified_at`).
- Middleware khusus `sso.callback` menangani token dari Sistem Akademik, cari/`firstOrCreate` user berdasarkan `external_id`, lalu login via Laravel Auth seperti biasa.

## 6. Package List (ringkas)
```
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require intervention/image
composer require laravel/socialite   # jika SSO pakai OAuth2 standar
composer require pestphp/pest --dev
npm install -D tailwindcss alpinejs   # jika pakai Opsi A
```

## 7. Kenapa Bukan Node.js/Python?
Karena Sistem Akademik sudah berjalan di PHP/Laravel, memilih stack yang sama:
- Menghindari overhead menjalankan 2 runtime berbeda di satu VPS kecil.
- Sharing logic autentikasi/SSO jauh lebih mudah (bisa reuse package/konvensi yang sama).
- Tim (atau AI agent) hanya perlu fasih satu ekosistem untuk maintenance jangka panjang.
