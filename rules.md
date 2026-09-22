# Rules.md — Panduan Kerja AI Agent / Kontributor

Dokumen ini adalah aturan wajib yang harus diikuti AI Agent saat melakukan "vibe coding" proyek CloudCampus Storage. Tujuannya: hasil kode konsisten, aman, dan mudah dilanjutkan manusia.

## 1. Prinsip Umum
1. **Ikuti PRD.md sebagai sumber kebenaran fitur.** Jangan menambah fitur di luar scope tanpa mencatatnya dulu di `todo.md`.
2. **Ikuti UIUX_BRIEF.md untuk tampilan.** Jangan mendesain layar baru yang tidak ada di brief tanpa alasan jelas.
3. **Backend API-first.** Semua logika bisnis (upload, share, quota check, dsb) wajib ditulis di `app/Services/*`, bukan langsung di Controller — supaya bisa dipanggil dari Web (Livewire) maupun API.
4. **Jangan hardcode konfigurasi.** Semua nilai yang bisa berubah (quota default, ukuran max upload, durasi expiry link, dsb) wajib lewat `.env` + file `config/cloudcampus.php`.
5. **Commit kecil & deskriptif.** Satu commit = satu perubahan logis (mis. "feat: tambah endpoint upload file"), bukan gabungan banyak fitur.

## 2. Standar Kode (PHP/Laravel)
- Ikuti **PSR-12** untuk gaya kode PHP.
- Ikuti konvensi penamaan Laravel: model singular (`File`, `Folder`), tabel jamak (`files`, `folders`), controller `PascalCase` + suffix `Controller`.
- Gunakan **Eloquent Resource** (`FileResource`, `FolderResource`) untuk semua response API — jangan return model mentah.
- Gunakan **Form Request** (`app/Http/Requests`) untuk validasi input, bukan validasi manual di controller.
- Gunakan **Policy** (`FilePolicy`, `FolderPolicy`) untuk otorisasi akses (misal: user hanya boleh hapus file miliknya sendiri).
- Query berat/list besar wajib pakai pagination, jangan `->get()` semua data.

## 3. Keamanan File (WAJIB, tidak bisa ditawar)
- Validasi **MIME type asli** file (bukan hanya ekstensi) sebelum disimpan.
- Rename file fisik di disk menjadi nama acak/hash (`uuid.ext`), simpan nama asli hanya di database.
- Simpan file di path terisolasi per user: `storage/app/users/{user_id}/...` — tidak boleh ada path traversal (`../`) dari input user manapun.
- Batasi ukuran upload sesuai `.env` (`MAX_UPLOAD_SIZE`), tolak di level validasi sebelum file diproses.
- Tautan berbagi (share link) wajib memakai token acak panjang (minimal 32 karakter), tidak boleh berbasis ID yang bisa ditebak.
- Setiap endpoint API wajib melewati middleware auth (Sanctum) kecuali endpoint publik share-link (yang punya pengecekan token & expiry sendiri).
- Jangan pernah menampilkan pesan error mentah (stack trace) ke user di environment production.

## 4. Autentikasi & SSO
- Jangan modifikasi tabel `users` inti Sistem Akademik — integrasi hanya lewat API/token, bukan akses database langsung ke sistem lain.
- Kolom `account_type` wajib diisi eksplisit saat user dibuat (`academic` atau `public`), tidak boleh null.
- User dengan `account_type = academic` **tidak boleh** bisa mengganti password lewat CloudCampus (password dikelola Sistem Akademik).

## 5. Testing
- Setiap Service baru wajib disertai test (Pest) minimal untuk: kasus sukses, kasus gagal (validasi), kasus otorisasi (user lain tidak boleh akses).
- Endpoint API wajib ada test HTTP (`postJson`, `getJson`, dsb) mengecek status code & struktur response.
- Jangan menandai task di `todo.md` selesai jika belum ada test yang lulus untuk fitur tersebut.

## 6. Frontend (Livewire/Alpine/Tailwind)
- Komponen Livewire dipecah kecil per fungsi (`FileList`, `UploadModal`, `ShareModal`) — hindari satu komponen raksasa.
- Semua warna/spacing mengikuti Style Guide di `UIUX_BRIEF.md` §7 — jangan pakai warna acak di luar palet.
- Loading state & error state wajib ada di setiap komponen yang memanggil server (upload, delete, share) — jangan biarkan UI diam tanpa feedback.

## 7. Dokumentasi & Update
- Setiap kali menambah endpoint API baru, update dokumentasi API (Postman collection atau anotasi `scramble`).
- Setiap kali menyelesaikan item di `todo.md`, centang item tersebut dan tambahkan catatan singkat jika ada penyesuaian dari rencana awal.
- Jika suatu keputusan teknis menyimpang dari `TECH_STACK.md`, catat alasannya di file tersebut (jangan diam-diam berbeda dari dokumen).

## 8. Larangan
- ❌ Jangan menyimpan file di luar struktur `storage/app/users/{user_id}` tanpa alasan kuat.
- ❌ Jangan expose kredensial (.env) ke repository atau ke response API.
- ❌ Jangan skip validasi kuota — user tidak boleh upload melebihi kuota walau dari sisi API.
- ❌ Jangan menghapus file secara permanen langsung — semua hapus melalui soft-delete + trash (30 hari) kecuali di trash sendiri (hapus permanen manual oleh user/admin).
