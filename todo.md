# Todo.md — Rencana Kerja Bertahap

Urutan disusun agar AI Agent bisa mengerjakan secara berurutan (setiap fase idealnya selesai + tertest sebelum lanjut fase berikut).

## Fase 0 — Setup Proyek
- [x] Init project Laravel 11 baru
- [x] Setup `.env` (DB, APP_URL, FILESYSTEM_DISK, MAX_UPLOAD_SIZE, dsb)
- [x] Install package inti: Sanctum, spatie/laravel-permission, intervention/image
- [x] Setup Tailwind CSS + Alpine.js + Livewire
- [x] Setup Pest untuk testing
- [x] Buat `config/cloudcampus.php` untuk konfigurasi custom (quota default, expiry default, dsb)

## Fase 1 — Database & Model
- [x] Migration `users` (tambah kolom `account_type`, `external_id`, `quota_bytes`, `used_bytes`)
- [x] Migration `folders` (nested: `parent_id`, `user_id`, `name`, timestamps, `deleted_at`)
- [x] Migration `files` (`folder_id`, `user_id`, `original_name`, `stored_name`, `mime_type`, `size`, `deleted_at`)
- [x] Migration `shares` (`shareable_type`, `shareable_id`, `token`, `permission`, `expires_at`, `shared_with_user_id` nullable)
- [x] Migration `activity_logs` (`user_id`, `action`, `subject_type`, `subject_id`, `meta`, `created_at`)
- [x] Model + relasi Eloquent untuk semua tabel di atas
- [x] Seeder role & permission dasar (super-admin, admin-kampus, user)

## Fase 2 — Autentikasi
- [x] Register/login/logout untuk `account_type = public` (pakai Laravel Breeze/Fortify sebagai basis, sesuaikan)
- [x] Verifikasi email untuk user publik
- [x] Endpoint/middleware `sso.callback` untuk menerima token dari Sistem Akademik
- [x] Logic `firstOrCreate` user akademik berdasarkan `external_id`
- [x] Middleware pembeda akses (akademik tidak bisa ganti password lokal)
- [x] Test: register publik, login SSO (mock), reject token SSO invalid

## Fase 3 — Manajemen Folder
- [x] `FolderService`: create, rename, delete (soft), move, list nested
- [x] `FolderPolicy`: hanya pemilik yang boleh modifikasi
- [x] Livewire component: File Explorer dengan breadcrumb navigation
- [x] API endpoint `/api/v1/folders` (CRUD)
- [x] Test service + endpoint

## Fase 4 — Upload & Manajemen File
- [x] `FileService`: validasi MIME, cek kuota, simpan ke `storage/app/users/{id}/...`, generate thumbnail (jika gambar)
- [x] Livewire component: Upload modal dengan progress bar (multi-file, drag-and-drop)
- [x] Fitur rename, pindah, hapus (soft-delete) file
- [x] Halaman Trash: restore & hapus permanen
- [x] API endpoint `/api/v1/files` (upload, list, download, delete)
- [x] Test: upload sukses, upload melebihi kuota (harus ditolak), upload MIME tidak diizinkan (harus ditolak)

## Fase 5 — Download
- [x] Download single file (stream response, bukan load penuh ke memory untuk file besar)
- [x] Download folder sebagai `.zip` (queued job untuk folder besar)
- [x] Test: download file, download folder kosong (harus tetap menghasilkan zip valid atau pesan jelas)

## Fase 6 — Berbagi File/Folder
- [x] `ShareService`: generate token, cek permission & expiry
- [x] Modal Share (tab Tautan & tab Orang) sesuai UIUX_BRIEF §5.4
- [x] Halaman publik akses via token (tanpa login, sesuai permission)
- [x] Halaman "Tautan Berbagi Saya" untuk kelola/hapus link
- [x] Halaman "Dibagikan dengan Saya"
- [x] Test: akses token valid, token expired (harus ditolak), permission view-only tidak bisa download

## Fase 7 — Kuota & Dashboard User
- [x] Hitung `used_bytes` otomatis saat upload/hapus (via observer/event)
- [x] Komponen dashboard kuota (progress bar + breakdown by tipe file)
- [x] Banner peringatan saat kuota >90%
- [x] Test: kuota terupdate benar setelah upload & hapus

## Fase 8 — Admin Panel
- [x] Dashboard statistik (total user, total storage, upload hari ini)
- [x] Manajemen user (list, filter by account_type, suspend, override quota)
- [x] Halaman Log Aktivitas dengan filter
- [x] Middleware/Policy khusus role admin
- [x] Test: user biasa tidak bisa akses route admin

## Fase 9 — Integrasi API dengan Sistem Akademik
- [x] Dokumentasi endpoint API (Postman collection atau Scramble)
- [x] Contoh skrip integrasi dari sisi Sistem Akademik (pseudo-code/cURL)
- [x] Rate limiting untuk API token
- [x] Test end-to-end: Sistem Akademik (mock) upload file atas nama user tertentu

## Fase 10 — Polishing & Deployment
- [x] Responsive check semua layar (mobile/tablet/desktop) sesuai UIUX_BRIEF §8
- [x] Audit keamanan checklist dari `rules.md` §3
- [x] Setup Nginx + PHP-FPM + Supervisor (queue) di VPS
- [x] Setup SSL (Let's Encrypt) & backup cron (DB + storage)
- [x] Smoke test di environment production sebelum go-live

## Backlog (v1.1 / v2 — belum dikerjakan sekarang)
- [ ] Password-protect share link
- [ ] Notifikasi email saat file dibagikan
- [ ] Version history file
- [ ] Migrasi storage ke S3/MinIO
- [ ] Real-time collaboration
- [ ] Mobile app
