<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ZipService
{
    /**
     * Membuat berkas arsip ZIP untuk sebuah folder secara rekursif.
     * Menangani hierarki subfolder serta folder kosong secara valid.
     *
     * @throws \RuntimeException
     */
    public function generateZipForFolder(Folder $folder, ?string $destinationPath = null): string
    {
        $diskName = config('cloudcampus.disk', 'local');
        $disk = Storage::disk($diskName);

        if (! $destinationPath) {
            $tempDir = storage_path('app/' . config('cloudcampus.temp_zip_path', 'temp_zips'));
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $uuid = Str::uuid();
            $destinationPath = $tempDir . DIRECTORY_SEPARATOR . "folder_{$folder->id}_{$uuid}.zip";
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($destinationPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            throw new \RuntimeException("Gagal membuka atau membuat berkas ZIP. Kode status: {$openResult}");
        }

        // Sanitasi nama direktori utama di dalam ZIP
        $rootName = $this->sanitizePathSegment($folder->name) ?: 'folder';
        $zip->addEmptyDir($rootName);

        // Rekursif masukkan berkas dan subfolder
        $this->addFolderContentsToZip($zip, $folder, $rootName, $disk);

        $zip->close();

        ActivityLog::record(
            'folder_download_zip',
            $folder,
            "Menghasilkan arsip ZIP untuk folder \"{$folder->name}\"",
            [
                'folder_id' => $folder->id,
                'total_size' => $this->calculateFolderSize($folder),
            ],
            Auth::id() ?? $folder->user_id
        );

        return $destinationPath;
    }

    /**
     * Memasukkan seluruh berkas dan subfolder ke dalam arsip ZIP secara rekursif.
     */
    protected function addFolderContentsToZip(ZipArchive $zip, Folder $folder, string $currentZipPath, $disk): void
    {
        // Masukkan semua berkas di level folder ini
        $files = $folder->files()->get();
        foreach ($files as $file) {
            if ($disk->exists($file->storage_path)) {
                $sanitizedFileName = $this->sanitizePathSegment($file->original_name);
                $entryZipPath = $currentZipPath . '/' . $sanitizedFileName;

                $stream = $disk->readStream($file->storage_path);
                if ($stream) {
                    $content = stream_get_contents($stream);
                    $zip->addFromString($entryZipPath, $content);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
        }

        // Masukkan semua subfolder secara rekursif
        $children = $folder->children()->get();
        foreach ($children as $child) {
            $sanitizedChildName = $this->sanitizePathSegment($child->name) ?: 'subfolder';
            $childZipPath = $currentZipPath . '/' . $sanitizedChildName;

            // Tambahkan entri direktori (menjamin subfolder kosong tetap valid di dalam ZIP)
            $zip->addEmptyDir($childZipPath);

            $this->addFolderContentsToZip($zip, $child, $childZipPath, $disk);
        }
    }

    /**
     * Menghitung total ukuran berkas di dalam folder beserta seluruh subfoldernya.
     */
    public function calculateFolderSize(Folder $folder): int
    {
        $size = (int) $folder->files()->sum('size');

        foreach ($folder->children()->get() as $child) {
            $size += $this->calculateFolderSize($child);
        }

        return $size;
    }

    /**
     * Memeriksa apakah ukuran folder melebihi ambang batas sehingga harus dioperasikan via background queue.
     */
    public function shouldQueue(Folder $folder): bool
    {
        $threshold = (int) config('cloudcampus.zip_sync_threshold_bytes', 50 * 1024 * 1024);

        return $this->calculateFolderSize($folder) > $threshold;
    }

    /**
     * Sanitasi nama berkas/folder agar tidak mengandung karakter ilegal di dalam path ZIP.
     */
    public function sanitizePathSegment(string $segment): string
    {
        return trim(preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $segment));
    }
}
