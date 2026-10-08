# Dokumentasi REST API — CloudCampus Storage
Versi API: `v1` | Autentikasi: `Laravel Sanctum Bearer Token`

---

## 1. Ikhtisar & Base URL
Layanan CloudCampus Storage menyediakan antarmuka REST API terstandarisasi untuk manajemen file, folder bertingkat, kuota, pembagian berkas (sharing), serta integrasi dengan Sistem Akademik Kampus (SIAKAD).

- **Base URL Lokal:** `http://127.0.0.1:8000/api/v1`
- **Base URL Produksi:** `https://cloud.kampus.ac.id/api/v1`
- **Format Pertukaran Data:** JSON (`Content-Type: application/json` & `Accept: application/json`).
- **Upload Berkas:** `multipart/form-data`.

---

## 2. Autentikasi & Otorisasi
Semua endpoint privat mewajibkan header:
```http
Authorization: Bearer <SANCTUM_API_TOKEN>
Accept: application/json
```

Token dapat di-generate melalui Sanctum token command atau via login API.
Rute publik (akses file via link token) **tidak memerlukan** header Authorization.

---

## 3. Rate Limiting (Pembatasan Laju Akses)
Sesuai kepatuhan rules.md §3 dan spesifikasi teknis:
- **Default Rate Limit:** **60 permintaan per menit** per token atau per alamat IP.
- **Header Respons:**
  - `X-RateLimit-Limit`: Batas maksimum permintaan dalam jendela waktu (mis. 60).
  - `X-RateLimit-Remaining`: Sisa kuota permintaan yang tersedia dalam jendela waktu aktif.
- **Status Kode Saat Melampaui:** `429 Too Many Requests`.

---

## 4. Format Respons & Penanganan Kesalahan (Error Handling)

### Respons Sukses
```json
{
  "data": { ... }
}
```

### Kesalahan Validasi (`422 Unprocessable Entity`)
```json
{
  "message": "Validasi gagal.",
  "errors": {
    "file": [
      "Kuota penyimpanan Anda tidak mencukupi untuk mengunggah berkas ini."
    ]
  }
}
```

### Kesalahan Otorisasi (`403 Forbidden`)
```json
{
  "message": "Anda tidak memiliki hak akses untuk mengunggah berkas atas nama pengguna lain."
}
```

### Tautan Kedaluwarsa (`410 Gone`)
```json
{
  "message": "Tautan berbagi ini telah kedaluwarsa atau tidak aktif."
}
```

---

## 5. Ringkasan Endpoint

### A. Folders
| Method | Endpoint | Deskripsi | Otorisasi |
|:---|:---|:---|:---|
| `GET` | `/folders` | Daftar folder (opsional query `parent_id`) | Sanctum |
| `POST` | `/folders` | Membuat folder baru | Sanctum |
| `GET` | `/folders/{id}` | Detail folder beserta isi | Sanctum |
| `PUT` | `/folders/{id}` | Mengubah nama (rename) folder | Sanctum (Owner/Admin) |
| `POST` | `/folders/{id}/move` | Memindahkan folder ke parent lain | Sanctum (Owner) |
| `GET` | `/folders/{id}/download` | Mengunduh seluruh folder sebagai arsip `.zip` | Sanctum |
| `DELETE` | `/folders/{id}` | Menghapus folder (soft-delete bersarang) | Sanctum (Owner) |

### B. Files
| Method | Endpoint | Deskripsi | Otorisasi |
|:---|:---|:---|:---|
| `GET` | `/files` | Daftar file (query `folder_id`, `search`, `external_id`) | Sanctum |
| `POST` | `/files` | Upload berkas mandiri / atas nama mahasiswa | Sanctum |
| `GET` | `/files/{id}` | Detail metadata berkas | Sanctum (Owner/Admin) |
| `PUT` | `/files/{id}` | Ubah nama berkas | Sanctum (Owner) |
| `POST` | `/files/{id}/move` | Pindahkan berkas ke folder lain | Sanctum (Owner) |
| `GET` | `/files/{id}/download` | Unduh berkas fisik (StreamedResponse) | Sanctum (Owner/Admin) |
| `DELETE` | `/files/{id}` | Pindahkan berkas ke Trash | Sanctum (Owner) |

### C. Shares (Berbagi File & Folder)
| Method | Endpoint | Deskripsi | Otorisasi |
|:---|:---|:---|:---|
| `GET` | `/shares` | Daftar link berbagi milik user | Sanctum |
| `POST` | `/shares` | Buat share link publik / private ke email | Sanctum |
| `DELETE` | `/shares/{id}` | Cabut / hapus tautan berbagi | Sanctum (Owner) |
| `GET` | `/public/shares/{token}` | Informasi preview berkas publik | Publik |
| `GET` | `/public/shares/{token}/download` | Unduh berkas dari link publik (permission: download) | Publik |

### D. Kuota Pengguna
| Method | Endpoint | Deskripsi | Otorisasi |
|:---|:---|:---|:---|
| `GET` | `/quota` | Statistik kuota & rincian kategori file | Sanctum |
| `POST` | `/quota/recalculate` | Sinkronisasi ulang `used_bytes` dengan disk | Sanctum |

### E. Integrasi Admin & Sistem Akademik
| Method | Endpoint | Deskripsi | Role Diperlukan |
|:---|:---|:---|:---|
| `POST` | `/files` (dengan `external_id`) | Upload atas nama mahasiswa/dosen | `admin-kampus` / `super-admin` |
| `GET` | `/files?external_id={NIM}` | Ambil daftar berkas milik mahasiswa tertentu | `admin-kampus` / `super-admin` |
| `GET` | `/admin/stats` | Statistik total storage & pengguna | Admin |
| `GET` | `/admin/users` | Daftar seluruh akun pengguna | Admin |
| `POST` | `/admin/users/{id}/suspend` | Tangguhkan akun user | Admin |
| `POST` | `/admin/users/{id}/quota` | Ubah batas kuota user | Admin |
| `GET` | `/admin/logs` | Audit trail aktivitas sistem | Admin |

---

## 6. Contoh Payload Khusus Integrasi Sistem Akademik

### Upload Tugas Mahasiswa via Server Akademik
```http
POST /api/v1/files HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 3|siakad-api-service-token...
Content-Type: multipart/form-data; boundary=----WebKitFormBoundary

------WebKitFormBoundary
Content-Disposition: form-data; name="file"; filename="Tugas1_20241001.pdf"
Content-Type: application/pdf

<binary_data>
------WebKitFormBoundary
Content-Disposition: form-data; name="external_id"

NIM20241001
------WebKitFormBoundary
Content-Disposition: form-data; name="user_name"

Ahmad Mahasiswa
------WebKitFormBoundary
Content-Disposition: form-data; name="user_email"

ahmad@kampus.ac.id
------WebKitFormBoundary--
```

**Respons Sukses (201 Created):**
```json
{
  "data": {
    "id": 14,
    "user_id": 8,
    "folder_id": null,
    "original_name": "Tugas1_20241001.pdf",
    "extension": "pdf",
    "mime_type": "application/pdf",
    "size": 154820,
    "formatted_size": "151.19 KB",
    "thumbnail_path": null,
    "is_favorite": false,
    "created_at": "2026-10-08T22:30:00.000000Z",
    "updated_at": "2026-10-08T22:30:00.000000Z"
  }
}
```
