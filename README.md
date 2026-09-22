# CloudCampus Storage

Sistem cloud storage yang bisa berjalan **stand-alone untuk publik** maupun **terintegrasi dengan Sistem Akademik kampus** (SSO + API).

## ✨ Fitur Utama
- 🔐 Login ganda: SSO otomatis untuk user akademik, register/login mandiri untuk user publik
- ⬆️ Upload file (single & multiple, drag-and-drop)
- ⬇️ Download file & folder (zip)
- 📁 Manajemen folder bertingkat (nested)
- 🔗 Berbagi file/folder via tautan (dengan permission & expiry) maupun ke user lain
- 📊 Kuota penyimpanan per user + dashboard pemakaian
- 🛠️ Panel admin: monitoring storage, manajemen user, log aktivitas
- 🔌 REST API agar Sistem Akademik bisa upload/download file secara programatik

## 📚 Dokumen Terkait
| Dokumen | Isi |
|---|---|
| [`PRD.md`](./PRD.md) | Kebutuhan produk lengkap, scope, user stories |
| [`UIUX_BRIEF.md`](./UIUX_BRIEF.md) | Persona, user flow, layout tiap layar, style guide |
| [`TECH_STACK.md`](./TECH_STACK.md) | Stack teknis & justifikasi pilihan |
| [`rules.md`](./rules.md) | Aturan coding untuk AI Agent / kontributor |
| [`todo.md`](./todo.md) | Rencana kerja bertahap (checklist) |

## 🧱 Tech Stack Singkat
- **Backend**: Laravel 11 (PHP 8.3), MySQL/MariaDB
- **Frontend**: Laravel Livewire + Alpine.js + Tailwind CSS (lihat opsi alternatif di TECH_STACK.md)
- **Auth**: Laravel Sanctum + custom SSO bridge ke Sistem Akademik
- **Storage**: Laravel Filesystem (disk lokal di VPS, didesain agar mudah pindah ke S3/MinIO)

## 🚀 Setup Awal (rencana)
```bash
git clone <repo-url> cloudcampus-storage
cd cloudcampus-storage
composer install
cp .env.example .env
php artisan key:generate
# atur DB_*, SSO_*, FILESYSTEM_DISK di .env
php artisan migrate
php artisan storage:link
npm install && npm run build
php artisan serve
```

## 📁 Struktur Direktori (rencana)
```
app/
  Models/          # User, File, Folder, Share
  Services/        # FileService, FolderService, ShareService, SsoService
  Http/
    Controllers/   # Web (Livewire) & Api (v1)
    Middleware/    # SsoCallback, CheckQuota
storage/
  app/users/{user_id}/...   # file fisik tersimpan di sini
routes/
  web.php
  api.php
```

## 🔒 Keamanan (ringkas)
- Validasi MIME type & ekstensi setiap upload
- Isolasi folder fisik per user (`storage/app/users/{user_id}`)
- Token API (Sanctum) untuk komunikasi dengan Sistem Akademik
- Tautan berbagi memakai token acak + opsi expiry

## 🗺️ Status Proyek
Tahap: **Perencanaan** — dokumen PRD, UI/UX, tech stack, dan rules sudah disusun sebagai bahan untuk AI Agent memulai development (lihat `todo.md` untuk urutan kerja).
