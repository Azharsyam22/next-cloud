# Laporan Audit Keamanan — CloudCampus Storage
Standar Acuan: `rules.md` §3 (Keamanan File, Data & Autentikasi)

---

## Ringkasan Eksekutif
Audit keamanan otomatis dan manual telah dijalankan terhadap codebase CloudCampus Storage. Seluruh 8 pilar keamanan wajib terverifikasi telah diimplementasikan dengan pengujian terotomatisasi (100% Lulus).

| No | Aturan Keamanan (`rules.md` §3 & §4) | Status | Bukti Implementasi / Tes |
|:---|:---|:---:|:---|
| 1 | Validasi MIME type asli (bukan hanya ekstensi) sebelum berkas disimpan | **LULUS** | `FileService::upload()`, ditolak dengan `422 Unprocessable Entity` jika MIME tidak terdaftar |
| 2 | Hash nama fisik di disk (`uuid.ext`), simpan nama asli hanya di database | **LULUS** | `Str::uuid()->toString()`, nama di disk dienkode acak |
| 3 | Isolasi path direktori per user (`storage/app/users/{id}/...`) & anti path traversal | **LULUS** | Input `../../` dinetralkan, tidak ada kebocoran direktori |
| 4 | Batasan ukuran upload sesuai `MAX_UPLOAD_SIZE` | **LULUS** | Divalidasi di `UploadFileRequest` & `FileService` sebelum disk IO |
| 5 | Tautan berbagi (share link) menggunakan token acak panjang (64 karakter) | **LULUS** | `ShareService` meng-generate token acak 64 karakter alphanumeric |
| 6 | Proteksi otentikasi Sanctum & Rate Limiting pada API | **LULUS** | `auth:sanctum` + `throttle:api` (60 req/min), status `401` & `429` |
| 7 | Proteksi akun akademik (tidak dapat mengganti password lokal) | **LULUS** | Middleware `PreventAcademicPasswordChange`, status `403 Forbidden` |
| 8 | Otorisasi kepemilikan file & folder | **LULUS** | `FilePolicy` & `FolderPolicy` mencegah akses silang antar pengguna |

---

## Rincian Temuan & Rekomendasi Hardening Produksi
1. **Penyembunyian Informasi Sensitif**:
   - Pastikan variabel `APP_DEBUG=false` pada lingkungan production `.env`.
   - Konfigurasi Nginx memblokir akses HTTP ke direktori tersembunyi (`.env`, `.git`).
2. **Rate Limiting**:
   - Endpoint publik share link dibatasi 60 request/menit untuk memitigasi brute-force token.
   - Endpoint terautentikasi dibatasi per user ID/IP.
3. **Penyimpanan Terisolasi**:
   - Berkas fisik mahasiswa/dosen tersimpan di disk lokal berformat `users/{user_id}/files/{uuid.ext}`, mencegah konflik nama dan eksploitasi path traversal.
