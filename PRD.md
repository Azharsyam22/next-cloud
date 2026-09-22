# PRD — CloudCampus Storage
Product Requirements Document | v1.0

## 1. Latar Belakang
Kampus membutuhkan layanan penyimpanan file (cloud storage) yang:
- Bisa berdiri sendiri (stand-alone) dan dipakai oleh publik/umum.
- Bisa terintegrasi dengan Sistem Akademik yang sudah ada (berbasis PHP/Laravel) sehingga mahasiswa/dosen bisa login otomatis (SSO) dan sistem akademik bisa menitipkan/mengambil file lewat API (misalnya berkas tugas, dokumen kelulusan, dsb).

## 2. Tujuan Produk
1. Menyediakan fitur inti cloud storage: upload, download, folder, share, auth.
2. Mendukung dua jalur pengguna: pengguna internal (via SSO akademik) dan pengguna publik (registrasi mandiri).
3. Desain modular agar backend/storage bisa dipakai ulang oleh sistem lain via API.
4. Berjalan efisien di server sendiri (VPS) dengan skala awal kecil (< 1000 user).

## 3. Target Pengguna & Peran
| Role | Sumber Akun | Deskripsi |
|---|---|---|
| Super Admin | Internal | Kelola seluruh sistem, quota, user |
| User Akademik | SSO dari Sistem Akademik | Mahasiswa/dosen/staff, login otomatis |
| User Publik | Registrasi mandiri | Umum, daftar via email + password |

## 4. Ruang Lingkup (Scope) v1
### In-scope
- Autentikasi ganda: SSO (akademik) + Register/Login mandiri (publik)
- Upload file (single & multiple, drag-and-drop)
- Download file (single) & folder (sebagai .zip)
- Buat, rename, hapus, pindah folder (nested folder)
- Rename, pindah, hapus (soft-delete + trash) file
- Preview file umum (gambar, PDF, dokumen kantor via viewer)
- Berbagi file: link publik/terbatas, permission (view/download), expiry link
- Berbagi internal (ke user lain by email)
- Kuota penyimpanan per user + dashboard pemakaian
- Admin panel dasar: kelola user, monitor storage, log aktivitas
- REST API agar Sistem Akademik dapat upload/download/list file secara programatik

### Out-of-scope v1 (masuk roadmap v2)
- Real-time collaborative editing
- Version history file
- Sinkronisasi desktop client / mobile app native
- Password-protected share link (opsional v1.1)
- Virus scanning otomatis (opsional, tergantung resource server)

## 5. User Stories Utama
- Sebagai mahasiswa, saya ingin login otomatis pakai akun akademik saya tanpa daftar ulang.
- Sebagai user publik, saya ingin daftar akun sendiri dengan email agar bisa menyimpan file pribadi.
- Sebagai user, saya ingin mengunggah banyak file sekaligus dan melihat progress upload.
- Sebagai user, saya ingin membuat folder bertingkat untuk mengorganisir file.
- Sebagai user, saya ingin membagikan link file/folder ke orang lain, dengan opsi kadaluarsa link.
- Sebagai admin, saya ingin melihat total pemakaian storage dan user mana yang mendekati kuota.
- Sebagai Sistem Akademik, saya ingin memanggil API untuk menyimpan berkas tugas mahasiswa langsung ke storage user terkait.

## 6. Kebutuhan Non-Fungsional
- **Keamanan**: HTTPS wajib, validasi MIME type & ekstensi file, batas ukuran upload, rate limiting, sanitasi nama file, isolasi folder per user.
- **Performa**: chunked/resumable upload untuk file besar, progress bar, queue untuk proses berat (generate thumbnail, zip folder).
- **Skalabilitas**: mulai dari local disk (VPS), tapi lapisan storage dibuat abstrak (Laravel Filesystem) agar mudah pindah ke S3/MinIO di masa depan.
- **Auditability**: log semua aksi penting (upload, delete, share, login) untuk kebutuhan audit kampus.
- **Ketersediaan**: target uptime 99% (skala kampus, single VPS + backup rutin).

## 7. Integrasi dengan Sistem Akademik
- **Autentikasi (SSO)**: Sistem Akademik bertindak sebagai Identity Provider. CloudCampus menerima token (JWT/OAuth2) dari Sistem Akademik, memverifikasi, lalu membuat/mengaitkan akun lokal berdasarkan NIM/NIP + email.
- **API Programatik**: endpoint REST (`/api/v1/files`, `/api/v1/folders`, dst) diamankan dengan token (Laravel Sanctum), memungkinkan Sistem Akademik meng-upload/mengambil berkas atas nama user tertentu.
- **Mode Berdiri Sendiri**: jika diakses tanpa konteks akademik, user publik memakai flow register/login mandiri — tabel user sama, dibedakan lewat kolom `account_type` (academic/public).

## 8. Batasan & Asumsi
- Storage awal memakai disk VPS — perlu monitoring kapasitas disk dan kebijakan kuota ketat.
- Skala awal ditujukan untuk satu kampus/prodi (<1000 user aktif).
- Sistem Akademik sudah berbasis Laravel/PHP sehingga integrasi library & konvensi bisa diselaraskan.

## 9. Metrik Sukses
- Tingkat keberhasilan upload > 98%
- Waktu rata-rata upload file 10MB < 5 detik (jaringan kampus normal)
- Adopsi: minimal 70% mahasiswa aktif memakai dalam 3 bulan pertama
- Zero insiden kebocoran file akibat kesalahan permission

## 10. Roadmap
- **v1 (MVP)**: seluruh fitur in-scope di atas.
- **v1.1**: password-protect share link, notifikasi email saat file dibagikan.
- **v2**: version history, real-time collaboration, migrasi ke object storage (S3/MinIO), mobile app.
