<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\ZipService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FolderWebController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mengunduh seluruh folder sebagai arsip .zip untuk pengguna web.
     */
    public function downloadZip(Folder $folder, ZipService $zipService): BinaryFileResponse
    {
        $this->authorize('view', $folder);

        $zipPath = $zipService->generateZipForFolder($folder);
        $safeName = ($zipService->sanitizePathSegment($folder->name) ?: 'folder') . '.zip';

        return response()->download($zipPath, $safeName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
