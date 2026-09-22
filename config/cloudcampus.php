<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CloudCampus Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Berisi konfigurasi terpusat untuk CloudCampus Storage sesuai aturan
    | rules.md §1.4 dan spesifikasi PRD.md / TECH_STACK.md.
    |
    */

    // Kuota penyimpanan default per user baru (dalam byte, default 5 GB)
    'default_quota_bytes' => (int) env('DEFAULT_QUOTA_BYTES', 5 * 1024 * 1024 * 1024),

    // Batas maksimal upload per file (dalam Kilobyte, default 100 MB = 102400 KB)
    'max_upload_size_kb' => (int) env('MAX_UPLOAD_SIZE', 102400),

    // Durasi kedaluwarsa default untuk tautan berbagi (dalam hari)
    'share_link_default_expiry_days' => (int) env('SHARE_LINK_DEFAULT_EXPIRY_DAYS', 7),

    // Masa retensi file di trash sebelum dihapus otomatis (dalam hari)
    'trash_retention_days' => (int) env('TRASH_RETENTION_DAYS', 30),

    // Root folder isolasi penyimpanan file per user di disk
    // Sesuai rules.md §3: storage/app/users/{user_id}/...
    'user_storage_path' => 'users',

    // Disk penyimpanan yang digunakan (default: local)
    'disk' => env('FILESYSTEM_DISK', 'local'),

    // Batas ambang ukuran folder untuk diproses via background queue (dalam byte, default 50 MB)
    'zip_sync_threshold_bytes' => (int) env('ZIP_SYNC_THRESHOLD_BYTES', 50 * 1024 * 1024),

    // Subdirektori penyimpanan arsip ZIP sementara
    'temp_zip_path' => 'temp_zips',

    // Tipe MIME yang diizinkan untuk diunggah
    'allowed_mime_types' => [
        // Dokumen
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        'text/markdown',

        // Gambar
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',

        // Arsip
        'application/zip',
        'application/x-rar-compressed',
        'application/x-tar',
        'application/x-7z-compressed',

        // Audio & Video
        'audio/mpeg',
        'audio/wav',
        'video/mp4',
        'video/webm',
    ],

    // Konfigurasi SSO Sistem Akademik
    'sso' => [
        'provider_url' => env('SSO_PROVIDER_URL'),
        'client_id' => env('SSO_CLIENT_ID'),
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'redirect_uri' => env('SSO_REDIRECT_URI'),
    ],
];
