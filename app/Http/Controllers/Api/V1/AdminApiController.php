<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminService;
use App\Services\QuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AdminApiController extends Controller
{
    public function __construct(
        protected AdminService $adminService,
        protected QuotaService $quotaService
    ) {}

    /**
     * Mengambil statistik sistem untuk admin.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->adminService->getSystemStats(),
        ]);
    }

    /**
     * Mengambil daftar pengguna terpaginasi dengan filter.
     */
    public function users(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'account_type', 'status', 'sort_by', 'sort_direction']);
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->adminService->getUsers($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Menangguhkan atau mengaktifkan akun pengguna.
     */
    public function toggleSuspend(Request $request, User $user): JsonResponse
    {
        try {
            $isSuspended = $this->adminService->toggleUserSuspension($user, $request->user());

            return response()->json([
                'success' => true,
                'message' => $isSuspended
                    ? "Akun {$user->name} berhasil ditangguhkan."
                    : "Akun {$user->name} berhasil diaktifkan kembali.",
                'is_suspended' => $isSuspended,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Menetapkan batas kuota baru untuk pengguna.
     */
    public function overrideQuota(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'quota_bytes' => 'required|integer|min:1',
        ]);

        try {
            $updatedUser = $this->adminService->overrideUserQuota($user, $validated['quota_bytes'], $request->user());

            return response()->json([
                'success' => true,
                'message' => "Kuota {$user->name} berhasil diperbarui menjadi {$this->quotaService->formatBytes($validated['quota_bytes'])}.",
                'data' => [
                    'id' => $updatedUser->id,
                    'name' => $updatedUser->name,
                    'quota_bytes' => $updatedUser->quota_bytes,
                    'formatted_quota' => $this->quotaService->formatBytes($updatedUser->quota_bytes),
                    'used_bytes' => $updatedUser->used_bytes,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mengambil daftar log aktivitas sistem terpaginasi.
     */
    public function logs(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'action', 'user_id']);
        $perPage = (int) $request->query('per_page', 20);

        $paginator = $this->adminService->getActivityLogs($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
