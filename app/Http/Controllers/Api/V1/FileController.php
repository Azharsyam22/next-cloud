<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\MoveFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use App\Http\Requests\File\UploadFileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
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
     * Mengambil daftar berkas milik pengguna aktif atau pengguna tertentu jika dipanggil oleh admin/sistem akademik.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $folderId = $request->query('folder_id');
        $folderId = $folderId !== null ? (int) $folderId : null;
        $search = $request->query('search');

        $targetUserId = $request->user()->id;

        // Dukungan untuk Sistem Akademik / Admin melihat file atas nama user tertentu
        if ($request->filled('external_id') || $request->filled('user_id')) {
            if ($request->user()->hasRole('super-admin') || $request->user()->hasRole('admin-kampus')) {
                if ($request->filled('external_id')) {
                    $targetUser = User::where('external_id', (string) $request->query('external_id'))->first();
                    $targetUserId = $targetUser ? $targetUser->id : 0;
                } else {
                    $targetUserId = (int) $request->query('user_id');
                }
            } else {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat berkas pengguna lain.');
            }
        }

        $query = File::where('user_id', $targetUserId)
            ->where('folder_id', $folderId);

        if (! empty($search)) {
            $query->where('original_name', 'like', '%'.$search.'%');
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(30);

        return FileResource::collection($files);
    }

    /**
     * Mengunggah berkas baru (mendukung upload atas nama user/mahasiswa oleh Sistem Akademik).
     */
    public function store(UploadFileRequest $request): JsonResponse
    {
        $folderId = $request->input('folder_id');
        $folderId = $folderId !== null ? (int) $folderId : null;

        $targetUser = $request->user();

        // Dukungan integrasi Sistem Akademik: upload berkas atas nama user tertentu (NIM/external_id atau user_id)
        if ($request->filled('external_id') || $request->filled('user_id')) {
            if ($request->user()->hasRole('super-admin') || $request->user()->hasRole('admin-kampus')) {
                if ($request->filled('external_id')) {
                    $extId = (string) $request->input('external_id');
                    $targetUser = User::where('external_id', $extId)->first();

                    if (! $targetUser) {
                        if ($request->filled('user_email')) {
                            // Auto-provision user akademik jika belum ada
                            $targetUser = User::create([
                                'external_id' => $extId,
                                'name' => $request->input('user_name', 'Mahasiswa '.$extId),
                                'email' => $request->input('user_email'),
                                'account_type' => 'academic',
                                'quota_bytes' => (int) config('cloudcampus.default_quota_bytes', 5368709120),
                                'used_bytes' => 0,
                                'email_verified_at' => now(),
                            ]);
                            $targetUser->assignRole('user');
                        } else {
                            return response()->json([
                                'message' => 'Pengguna akademik dengan external_id "'.$extId.'" tidak ditemukan.',
                            ], 404);
                        }
                    }
                } else {
                    $targetUser = User::findOrFail((int) $request->input('user_id'));
                }
            } else {
                return response()->json([
                    'message' => 'Anda tidak memiliki hak akses untuk mengunggah berkas atas nama pengguna lain.',
                ], 403);
            }
        }

        $file = $this->fileService->upload(
            $targetUser,
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
