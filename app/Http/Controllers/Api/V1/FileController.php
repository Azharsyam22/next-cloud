<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\MoveFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use App\Http\Requests\File\UploadFileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Mengambil daftar berkas milik pengguna aktif.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $folderId = $request->query('folder_id');
        $folderId = $folderId !== null ? (int) $folderId : null;
        $search = $request->query('search');

        $query = File::where('user_id', $request->user()->id)
            ->where('folder_id', $folderId);

        if (! empty($search)) {
            $query->where('original_name', 'like', '%'.$search.'%');
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(30);

        return FileResource::collection($files);
    }

    /**
     * Mengunggah berkas baru.
     */
    public function store(UploadFileRequest $request): JsonResponse
    {
        $folderId = $request->input('folder_id');
        $folderId = $folderId !== null ? (int) $folderId : null;

        $file = $this->fileService->upload(
            $request->user(),
            $request->file('file'),
            $folderId
        );

        return (new FileResource($file))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mengambil metadata detail berkas.
     */
    public function show(File $file): FileResource
    {
        $this->authorize('view', $file);

        return new FileResource($file);
    }

    /**
     * Mengunduh berkas fisik.
     */
    public function download(File $file): StreamedResponse
    {
        $this->authorize('download', $file);

        return $this->fileService->download($file);
    }

    /**
     * Mengubah nama berkas.
     */
    public function update(UpdateFileRequest $request, File $file): FileResource
    {
        $this->authorize('update', $file);

        $file = $this->fileService->rename($file, $request->input('name'));

        return new FileResource($file);
    }

    /**
     * Memindahkan berkas ke folder lain.
     */
    public function move(MoveFileRequest $request, File $file): FileResource
    {
        $this->authorize('update', $file);

        $folderId = $request->input('folder_id');
        $folderId = $folderId !== null ? (int) $folderId : null;

        $file = $this->fileService->move($file, $folderId);

        return new FileResource($file);
    }

    /**
     * Menghapus berkas secara soft-delete.
     */
    public function destroy(File $file): JsonResponse
    {
        $this->authorize('delete', $file);

        $this->fileService->delete($file);

        return response()->json([
            'message' => 'Berkas "'.$file->original_name.'" berhasil dipindahkan ke tempat sampah.',
        ]);
    }
}
