# Panduan Integrasi Sistem Akademik (SIAKAD) — CloudCampus Storage

Dokumen ini menjelaskan tata cara menghubungkan **Sistem Akademik Kampus (SIAKAD)** dengan **CloudCampus Storage**, mencakup:
1. **Single Sign-On (SSO)** untuk login otomatis mahasiswa & dosen.
2. **Server-to-Server REST API** untuk menitipkan berkas tugas, KRS, dokumen kelulusan (SKPI/Ijazah) langsung ke penyimpanan mahasiswa.

---

## 1. Integrasi Single Sign-On (SSO)

CloudCampus bertindak sebagai **Service Provider**, dan Sistem Akademik bertindak sebagai **Identity Provider (IdP)**.

### Alur Kerja SSO:
1. Mahasiswa mengklik "Login Akun Kampus" di CloudCampus.
2. CloudCampus mengarahkan browser ke URL Portal Akademik:
   `https://siakad.kampus.ac.id/oauth/authorize?client_id=...&redirect_uri=https://cloud.kampus.ac.id/auth/sso/callback&state=XYZ`
3. Setelah mahasiswa terotentikasi di Sistem Akademik, sistem akademik menghasilkan **Signed JWT Token** dan mengarahkan kembali ke:
   `https://cloud.kampus.ac.id/auth/sso/callback?token=<JWT_TOKEN>`
4. CloudCampus memverifikasi signature token menggunakan secret bersama (`SSO_CLIENT_SECRET`), memvalidasi masa berlaku (`exp`), lalu memanggil `firstOrCreate` akun lokal dengan `account_type = academic` dan mengarahkan ke dashboard.

### Spesifikasi Payload JWT Token:
```json
{
  "external_id": "NIM20241001",
  "name": "Ahmad Mahasiswa",
  "email": "ahmad@kampus.ac.id",
  "role": "mahasiswa",
  "exp": 1775685600
}
```

### Contoh Kode PHP Pembuat Token SSO (di sisi SIAKAD):
```php
<?php

function generateCloudCampusToken(array $student, string $secret): string
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'external_id' => $student['nim'],
        'name'        => $student['nama'],
        'email'       => $student['email'],
        'exp'         => time() + 300, // Valid selama 5 menit
    ];

    $b64Url = fn($data) => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

    $headerEncoded  = $b64Url(json_encode($header));
    $payloadEncoded = $b64Url(json_encode($payload));
    $signature      = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
    $sigEncoded     = $b64Url($signature);

    return "$headerEncoded.$payloadEncoded.$sigEncoded";
}

// Penggunaan:
$token = generateCloudCampusToken([
    'nim'   => '2024001',
    'nama'  => 'Ahmad Mahasiswa',
    'email' => 'ahmad@kampus.ac.id',
], 'SECRET_KAMPUS_BERSAMA_MINIMAL_32_KARAKTER');

header("Location: https://cloud.kampus.ac.id/auth/sso/callback?token=" . $token);
exit;
```

---

## 2. Integrasi Server-to-Server via REST API

Sistem Akademik dapat melakukan operasi file secara programatik menggunakan token Sanctum yang memiliki hak akses `admin-kampus` atau `super-admin`.

### A. Upload Berkas atas Nama Mahasiswa (cURL)
Gunakan cURL berikut saat mahasiswa mengunggah tugas di portal akademik:

```bash
curl -X POST "https://cloud.kampus.ac.id/api/v1/files" \
  -H "Authorization: Bearer 3|siakad-service-token-secret" \
  -H "Accept: application/json" \
  -F "file=@/var/tmp/tugas_basis_data.pdf" \
  -F "external_id=NIM20241001" \
  -F "user_name=Ahmad Mahasiswa" \
  -F "user_email=ahmad@kampus.ac.id"
```

**Hasil:**
- Berkas tersimpan di folder terisolasi milik mahasiswa `users/{id}/files/...`.
- Kuota penyimpanan mahasiswa terpotong sesuai ukuran berkas.
- Mahasiswa dapat melihat berkas ini langsung saat login ke CloudCampus Storage.

---

### B. Mengambil Daftar Berkas Mahasiswa (cURL)
Dosen atau admin akademik dapat melihat berkas mahasiswa melalui API:

```bash
curl -X GET "https://cloud.kampus.ac.id/api/v1/files?external_id=NIM20241001" \
  -H "Authorization: Bearer 3|siakad-service-token-secret" \
  -H "Accept: application/json"
```

---

### C. Mengunduh Berkas Mahasiswa (cURL)
```bash
curl -X GET "https://cloud.kampus.ac.id/api/v1/files/42/download" \
  -H "Authorization: Bearer 3|siakad-service-token-secret" \
  --output "berkas_tugas_42.pdf"
```

---

### D. Mengambil Informasi Kuota Mahasiswa
```bash
curl -X GET "https://cloud.kampus.ac.id/api/v1/quota" \
  -H "Authorization: Bearer 3|siakad-service-token-secret" \
  -H "Accept: application/json"
```

---

## 3. Kelas Helper SDK Siap Pakai (PHP / Laravel)
Tersedia kelas klien PHP mandiri di:
[`docs/api/examples/CloudCampusClient.php`](file:///c:/Vscode/next-cloud/docs/api/examples/CloudCampusClient.php)

Anda cukup meng-copy file tersebut ke aplikasi SIAKAD Anda dan menggunakannya langsung:
```php
use App\Services\CloudCampusClient;

$client = new CloudCampusClient([
    'base_url' => 'https://cloud.kampus.ac.id',
    'api_token' => env('CLOUDCAMPUS_API_TOKEN'),
]);

// 1. Upload tugas mahasiswa
$result = $client->uploadStudentFile(
    nim: '20241001',
    filePath: storage_path('app/uploads/tugas.pdf'),
    studentName: 'Ahmad Mahasiswa',
    studentEmail: 'ahmad@kampus.ac.id'
);

// 2. Ambil daftar file mahasiswa
$files = $client->getStudentFiles(nim: '20241001');

// 3. Download berkas
$client->downloadFile(fileId: 42, saveToPath: '/tmp/tugas_mhs.pdf');
```
