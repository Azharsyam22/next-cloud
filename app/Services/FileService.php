<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileService
{
    /**
     * Mengunggah berkas baru dengan validasi MIME, proteksi kuota, dan hashing nama fisik.
     *
     * @throws ValidationException
     */
    public function upload(User $user, UploadedFile $uploadedFile, ?int $folderId = null): File
    {
        // 1. Cek folder tujuan jika ditentukan
        if ($folderId !== null) {
            $folder = Folder::where('id', $folderId)
                ->where('user_id', $user->id)
                ->first();

            if (! $folder) {
                throw ValidationException::withMessages([
                    'folder_id' => ['Folder tujuan tidak ditemukan atau bukan milik Anda.'],
                ]);
            }
        }

        // 2. Cek batas ukuran berkas dari config (default: 100 MB)
        $fileSize = $uploadedFile->getSize();
        $maxSizeKb = (int) config('cloudcampus.max_upload_size_kb', 102400);

        if ($fileSize > ($maxSizeKb * 1024)) {
            $maxMb = round($maxSizeKb / 1024, 1);
            throw ValidationException::withMessages([
                'file' => ["Ukuran berkas melebihi batas maksimal yang diizinkan ({$maxMb} MB)."],
            ]);
        }

        // 3. Cek kuota penyimpanan pengguna (rules.md §8)
        if (! $user->hasQuotaFor($fileSize)) {
            throw ValidationException::withMessages([
                'file' => ['Kuota penyimpanan Anda tidak mencukupi untuk mengunggah berkas ini.'],
            ]);
        }

        // 4. Validasi MIME type asli berkas (rules.md §3)
        $mimeType = $uploadedFile->getMimeType() ?: 'application/octet-stream';
        $allowedMimes = config('cloudcampus.allowed_mime_types', []);

        if (! empty($allowedMimes) && ! in_array($mimeType, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => ["Tipe berkas ({$mimeType}) tidak diizinkan untuk diunggah ke sistem."],
            ]);
        }

        // 5. Sanitasi nama asli dan buat nama fisik acak (UUID) untuk disk (rules.md §3)
        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_BASENAME);
        $extension = $uploadedFile->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION);
        $uuid = Str::uuid()->toString();
        $storedName = $extension ? "{$uuid}.{$extension}" : $uuid;

        // 6. Tentukan path fisik terisolasi: storage/app/users/{user_id}/files/{uuid.ext}
        $diskName = config('cloudcampus.disk', 'local');
        $relativeDir = "users/{$user->id}/files";
        $storagePath = "{$relativeDir}/{$storedName}";

        // Simpan file fisik ke storage
        Storage::disk($diskName)->putFileAs($relativeDir, $uploadedFile, $storedName);

        // 7. Generate thumbnail jika file bertipe gambar
        $thumbnailPath = null;
        if (str_starts_with($mimeType, 'image/') && extension_loaded('gd')) {
            $thumbnailPath = $this->generateThumbnail($uploadedFile, $user->id, $uuid, $diskName);
        }

        // 8. Buat entitas File di database
        $file = File::create([
            'user_id' => $user->id,
            'folder_id' => $folderId,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'storage_path' => $storagePath,
            'mime_type' => $mimeType,
            'size' => $fileSize,
            'thumbnail_path' => $thumbnailPath,
            'is_favorite' => false,
        ]);

        // 9. Rekam riwayat audit
        ActivityLog::record(
            'file_upload',
            $file,
            "Mengunggah berkas \"{$file->original_name}\"",
            [
                'size' => $fileSize,
                'folder_id' => $folderId,
                'mime_type' => $mimeType,
            ],
            $user->id
        );

        return $file;
    }

    /**
     * Mengubah nama berkas.
     */
    public function rename(File $file, string $newName): File
    {
        $newName = trim($newName);

        $originalExt = pathinfo($file->original_name, PATHINFO_EXTENSION);
        $newExt = pathinfo($newName, PATHINFO_EXTENSION);

        if (empty($newExt) && ! empty($originalExt)) {
            $newName .= '.' . $originalExt;
        }

        if ($newName === $file->original_name) {
            return $file;
        }

        $oldName = $file->original_name;
        $file->update(['original_name' => $newName]);

        ActivityLog::record(
            'file_rename',
            $file,
            "Mengubah nama berkas dari \"{$oldName}\" menjadi \"{$newName}\"",
            ['old_name' => $oldName, 'new_name' => $newName],
            $file->user_id
        );

        return $file;
    }

    /**
     * Memindahkan berkas ke folder lain.
     *
     * @throws ValidationException
     */
    public function move(File $file, ?int $newFolderId): File
    {
        if ($newFolderId === $file->folder_id) {
            return $file;
        }

        if ($newFolderId !== null) {
            $targetFolder = Folder::where('id', $newFolderId)
                ->where('user_id', $file->user_id)
                ->first();

            if (! $targetFolder) {
                throw ValidationException::withMessages([
                    'folder_id' => ['Folder tujuan tidak ditemukan atau bukan milik Anda.'],
                ]);
            }
        }

        $oldFolderId = $file->folder_id;
        $file->update(['folder_id' => $newFolderId]);

        ActivityLog::record(
            'file_move',
            $file,
            "Memindahkan berkas \"{$file->original_name}\"",
            ['old_folder_id' => $oldFolderId, 'new_folder_id' => $newFolderId],
            $file->user_id
        );

        return $file;
    }

    /**
     * Menghapus berkas secara soft-delete (ke tempat sampah).
     */
    public function delete(File $file): bool
    {
        $file->delete();

        ActivityLog::record(
            'file_delete',
            $file,
            "Memindahkan berkas \"{$file->original_name}\" ke tempat sampah",
            [],
            $file->user_id
        );

        return true;
    }

    /**
     * Memulihkan berkas dari tempat sampah.
     */
    public function restore(File $file): bool
    {
        $file->restore();

        ActivityLog::record(
            'file_restore',
            $file,
            "Memulihkan berkas \"{$file->original_name}\" dari tempat sampah",
            [],
            $file->user_id
        );

        return true;
    }

    /**
     * Menghapus berkas secara permanen (menghapus file fisik dari disk dan membebaskan kuota).
     */
    public function permanentDelete(File $file): bool
    {
        $diskName = config('cloudcampus.disk', 'local');

        // Hapus file fisik dari disk
        if (Storage::disk($diskName)->exists($file->storage_path)) {
            Storage::disk($diskName)->delete($file->storage_path);
        }

        // Hapus thumbnail fisik jika ada
        if ($file->thumbnail_path && Storage::disk($diskName)->exists($file->thumbnail_path)) {
            Storage::disk($diskName)->delete($file->thumbnail_path);
        }

        $fileName = $file->original_name;
        $userId = $file->user_id;

        // Force delete dari database (FileObserver otomatis mengurangi used_bytes pengguna)
        $file->forceDelete();

        ActivityLog::record(
            'file_force_delete',
            null,
            "Menghapus permanen berkas \"{$fileName}\"",
            [],
            $userId
        );

        return true;
    }

    /**
     * Mengunduh berkas dengan stream response yang aman.
     *
     * @throws ValidationException
     */
    public function download(File $file): StreamedResponse
    {
        $diskName = config('cloudcampus.disk', 'local');

        if (! Storage::disk($diskName)->exists($file->storage_path)) {
            throw ValidationException::withMessages([
                'file' => ['Berkas fisik tidak ditemukan di sistem penyimpanan.'],
            ]);
        }

        ActivityLog::record(
            'file_download',
            $file,
            "Mengunduh berkas \"{$file->original_name}\"",
            ['size' => $file->size],
            $file->user_id
        );

        return response()->streamDownload(function () use ($diskName, $file) {
            $stream = Storage::disk($diskName)->readStream($file->storage_path);
            if ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, $file->original_name, [
            'Content-Type' => $file->mime_type,
            'Content-Length' => $file->size,
        ]);
    }

    /**
     * Membuat thumbnail gambar menggunakan Intervention Image.
     */
    protected function generateThumbnail(UploadedFile $uploadedFile, int $userId, string $uuid, string $diskName): ?string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($uploadedFile->getRealPath());

            // Crop thumbnail persegi 200x200
            $image->cover(200, 200);
            $encoded = $image->toWebp(80);

            $thumbName = "{$uuid}.webp";
            $relativeDir = "users/{$userId}/thumbnails";
            $thumbPath = "{$relativeDir}/{$thumbName}";

            Storage::disk($diskName)->put($thumbPath, (string) $encoded);

            return $thumbPath;
        } catch (\Throwable $e) {
            // Jika ada kendala pemrosesan gambar, fallback tanpa thumbnail (graceful failure)
            return null;
        }
    }
}
