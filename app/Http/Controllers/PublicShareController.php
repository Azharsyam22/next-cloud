<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Services\FileService;
use App\Services\ShareService;
use App\Services\ZipService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicShareController extends Controller
{
    /**
     * Menampilkan halaman publik untuk berkas atau folder yang dibagikan.
     */
    public function show(string $token, ShareService $shareService)
    {
        $share = $shareService->validateAccess($token, Auth::user());
        $item = $share->shareable;

        $folderContents = null;
        if ($item instanceof Folder) {
            $folderContents = [
                'folders' => $item->children()->orderBy('name')->get(),
                'files' => $item->files()->orderBy('created_at', 'desc')->get(),
            ];
        }

        return view('shares.public-share', [
            'share' => $share,
            'item' => $item,
            'isFolder' => $item instanceof Folder,
            'folderContents' => $folderContents,
        ]);
    }

    /**
     * Mengalirkan pratinjau inline berkas gambar/PDF secara langsung.
     */
    public function preview(string $token, ShareService $shareService)
    {
        $share = $shareService->validateAccess($token, Auth::user());
        $file = $share->shareable;

        if (! ($file instanceof File)) {
            abort(400, 'Pratinjau hanya tersedia untuk berkas.');
        }

        $diskName = config('cloudcampus.disk', 'local');
        $disk = Storage::disk($diskName);

        if (! $disk->exists($file->storage_path)) {
            abort(404, 'Berkas fisik tidak ditemukan.');
        }

        return $disk->response($file->storage_path, $file->original_name, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->original_name . '"',
        ]);
    }

    /**
     * Mengunduh berkas atau folder (sebagai ZIP) melalui tautan berbagi.
     */
    public function download(string $token, ShareService $shareService, FileService $fileService, ZipService $zipService): StreamedResponse|BinaryFileResponse
    {
        $share = $shareService->validateAccess($token, Auth::user());

        if (! $share->canDownload()) {
            abort(403, 'Izin pengunduhan tidak diaktifkan untuk tautan berbagi ini.');
        }

        $item = $share->shareable;

        if ($item instanceof File) {
            return $fileService->download($item);
        }

        if ($item instanceof Folder) {
            $zipPath = $zipService->generateZipForFolder($item);
            $safeName = ($zipService->sanitizePathSegment($item->name) ?: 'folder') . '.zip';

            return response()->download($zipPath, $safeName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        }

        abort(400, 'Tipe item tidak valid.');
    }

    /**
     * Mengunduh berkas individual yang berada di dalam folder yang dibagikan.
     */
    public function downloadFile(string $token, File $file, ShareService $shareService, FileService $fileService): StreamedResponse
    {
        $share = $shareService->validateAccess($token, Auth::user());

        if (! $share->canDownload()) {
            abort(403, 'Izin pengunduhan tidak diaktifkan untuk tautan berbagi ini.');
        }

        $item = $share->shareable;

        if (! ($item instanceof Folder)) {
            abort(400, 'Tautan ini bukan tautan folder.');
        }

        // Pastikan berkas benar-benar milik folder ini
        if ($file->folder_id !== $item->id && ! $this->isFileDescendantOfFolder($file, $item)) {
            abort(403, 'Berkas ini tidak termasuk dalam folder yang dibagikan.');
        }

        return $fileService->download($file);
    }

    /**
     * Memeriksa apakah berkas berada di dalam hierarki folder yang dibagikan.
     */
    protected function isFileDescendantOfFolder(File $file, Folder $parentFolder): bool
    {
        $current = $file->folder;

        while ($current !== null) {
            if ($current->id === $parentFolder->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }
}
