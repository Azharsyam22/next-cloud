<?php

namespace App\Services;

/**
 * CloudCampusClient — Client SDK sederhana untuk integrasi Sistem Akademik (SIAKAD) dengan CloudCampus Storage.
 */
class CloudCampusClient
{
    protected string $baseUrl;
    protected string $apiToken;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://127.0.0.1:8000', '/');
        $this->apiToken = $config['api_token'] ?? '';
    }

    /**
     * Mengunggah berkas tugas / dokumen atas nama mahasiswa ke CloudCampus Storage.
     */
    public function uploadStudentFile(
        string $nim,
        string $filePath,
        ?string $studentName = null,
        ?string $studentEmail = null,
        ?int $folderId = null
    ): array {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("Berkas fisik tidak ditemukan: {$filePath}");
        }

        $cFile = new \CURLFile($filePath, mime_content_type($filePath) ?: 'application/octet-stream', basename($filePath));

        $postData = [
            'file' => $cFile,
            'external_id' => $nim,
        ];

        if ($studentName) {
            $postData['user_name'] = $studentName;
        }

        if ($studentEmail) {
            $postData['user_email'] = $studentEmail;
        }

        if ($folderId) {
            $postData['folder_id'] = $folderId;
        }

        return $this->request('POST', '/api/v1/files', $postData, true);
    }

    /**
     * Mengambil daftar berkas milik mahasiswa tertentu.
     */
    public function getStudentFiles(string $nim, ?int $folderId = null, ?string $search = null): array
    {
        $params = ['external_id' => $nim];
        if ($folderId !== null) {
            $params['folder_id'] = $folderId;
        }
        if ($search !== null) {
            $params['search'] = $search;
        }

        $query = http_build_query($params);
        return $this->request('GET', "/api/v1/files?{$query}");
    }

    /**
     * Mengunduh berkas fisik berdasarkan ID berkas dan menyimpannya ke path tujuan lokal.
     */
    public function downloadFile(int $fileId, string $saveToPath): bool
    {
        $url = "{$this->baseUrl}/api/v1/files/{$fileId}/download";
        $fp = fopen($saveToPath, 'w+');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->apiToken}",
        ]);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (! $success || $httpCode >= 400) {
            if (file_exists($saveToPath)) {
                unlink($saveToPath);
            }
            throw new \RuntimeException("Gagal mengunduh berkas (HTTP Code: {$httpCode})");
        }

        return true;
    }

    /**
     * Helper request cURL ke server CloudCampus Storage.
     */
    protected function request(string $method, string $path, array $data = [], bool $isMultipart = false): array
    {
        $url = "{$this->baseUrl}{$path}";
        $ch = curl_init();

        $headers = [
            "Authorization: Bearer {$this->apiToken}",
            'Accept: application/json',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST') {
            if ($isMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL Error: {$error}");
        }

        $decoded = json_decode((string) $response, true);

        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? "Request gagal dengan status {$httpCode}";
            throw new \RuntimeException("API Error [{$httpCode}]: {$msg}");
        }

        return $decoded ?? [];
    }
}
