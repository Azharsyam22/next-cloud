<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Share\CreateShareRequest;
use App\Http\Requests\Share\UpdateShareRequest;
use App\Http\Resources\ShareResource;
use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use App\Services\FileService;
use App\Services\ShareService;
use App\Services\ZipService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShareController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ShareService $shareService
    ) {}

    /**
     * Mengambil daftar tautan berbagi yang dibuat oleh pengguna terautentikasi.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $shares = $this->shareService->getSharedByMe($request->user());

        return ShareResource::collection($shares);
    }

    /**
     * Membuat tautan berbagi baru (publik atau privat).
     */
    public function store(CreateShareRequest $request): JsonResponse
    {
        $type = $request->input('type');
        $id = (int) $request->input('id');

        $model = $type === 'file'
            ? File::where('id', $id)->firstOrFail()
            : Folder::where('id', $id)->firstOrFail();

        $this->authorize('view', $model);

        $permission = $request->input('permission', 'view');
        $expiryDays = $request->filled('expires_in_days') ? (int) $request->input('expires_in_days') : null;

        if ($request->filled('recipient_email')) {
            $recipient = User::where('email', $request->input('recipient_email'))->firstOrFail();
            $share = $this->shareService->shareWithUser($model, $request->user(), $recipient, $permission, $expiryDays);
        } else {
            $share = $this->shareService->createPublicShare($model, $request->user(), $permission, $expiryDays);
        }

        return (new ShareResource($share))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mengambil detail satu tautan berbagi.
     */
    public function show(Share $share): ShareResource
    {
        $this->authorize('view', $share);

        return new ShareResource($share);
    }

    /**
     * Memperbarui pengaturan tautan berbagi.
     */
    public function update(UpdateShareRequest $request, Share $share): ShareResource
    {
        $this->authorize('update', $share);

        $share = $this->shareService->updateShare($share, $request->validated());

        return new ShareResource($share);
    }

    /**
     * Menghapus tautan berbagi.
     */
    public function destroy(Share $share): JsonResponse
    {
        $this->authorize('delete', $share);

        $this->shareService->deleteShare($share);

        return response()->json([
            'message' => 'Tautan berbagi berhasil dihapus.',
        ]);
    }

    /**
     * Mengakses metadata tautan berbagi publik via API tanpa login.
     */
    public function publicShow(string $token, Request $request): ShareResource
    {
        $share = $this->shareService->validateAccess($token, $request->user());

        return new ShareResource($share);
    }

    /**
     * Mengunduh berkas atau folder via API publik menggunakan token.
     */
    public function publicDownload(string $token, Request $request, FileService $fileService, ZipService $zipService)
    {
        $share = $this->shareService->validateAccess($token, $request->user());

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
}
