<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Folder\MoveFolderRequest;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Http\Resources\FolderResource;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FolderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected FolderService $folderService
    ) {}

    /**
     * Mengambil daftar folder milik pengguna aktif.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentId = $request->query('parent_id');
        $parentId = $parentId !== null ? (int) $parentId : null;
        $search = $request->query('search');

        $query = Folder::where('user_id', $request->user()->id)
            ->where('parent_id', $parentId)
            ->withCount(['children', 'files']);

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $folders = $query->orderBy('name')->get();

        return FolderResource::collection($folders);
    }

    /**
     * Membuat folder baru.
     */
    public function store(StoreFolderRequest $request): JsonResponse
    {
        $folder = $this->folderService->create(
            $request->user(),
            $request->input('name'),
            $request->input('parent_id'),
            $request->input('color')
        );

        return (new FolderResource($folder))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mengambil detail folder tunggal.
     */
    public function show(Folder $folder): FolderResource
    {
        $this->authorize('view', $folder);

        $folder->loadCount(['children', 'files']);

        return new FolderResource($folder);
    }

    /**
     * Mengubah nama atau warna folder.
     */
    public function update(UpdateFolderRequest $request, Folder $folder): FolderResource
    {
        $this->authorize('update', $folder);

        if ($request->has('name') && $request->input('name') !== $folder->name) {
            $folder = $this->folderService->rename($folder, $request->input('name'));
        }

        if ($request->has('color')) {
            $folder->update(['color' => $request->input('color')]);
        }

        return new FolderResource($folder);
    }

    /**
     * Menghapus folder secara soft-delete.
     */
    public function destroy(Folder $folder): JsonResponse
    {
        $this->authorize('delete', $folder);

        $this->folderService->delete($folder);

        return response()->json([
            'message' => 'Folder "'.$folder->name.'" berhasil dipindahkan ke tempat sampah.',
        ]);
    }

    /**
     * Memindahkan folder ke parent tujuan baru.
     */
    public function move(MoveFolderRequest $request, Folder $folder): FolderResource
    {
        $this->authorize('update', $folder);

        $targetParentId = $request->input('target_parent_id');
        $targetParentId = $targetParentId !== null ? (int) $targetParentId : null;

        $folder = $this->folderService->move($folder, $targetParentId);

        return new FolderResource($folder);
    }

    /**
     * Mengunduh folder sebagai arsip ZIP melalui API.
     */
    public function download(Folder $folder, Request $request, \App\Services\ZipService $zipService): mixed
    {
        $this->authorize('view', $folder);

        if ($request->boolean('async') || $zipService->shouldQueue($folder)) {
            \App\Jobs\GenerateFolderZipJob::dispatch($folder, $request->user());

            return response()->json([
                'message' => 'Proses pembuatan arsip ZIP sedang diproses di latar belakang.',
                'status' => 'queued',
            ], 202);
        }

        $zipPath = $zipService->generateZipForFolder($folder);
        $safeName = ($zipService->sanitizePathSegment($folder->name) ?: 'folder') . '.zip';

        return response()->download($zipPath, $safeName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
