<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Mengunduh berkas melalui antarmuka web.
     */
    public function download(File $file): StreamedResponse
    {
        $this->authorize('download', $file);

        return $this->fileService->download($file);
    }
}
