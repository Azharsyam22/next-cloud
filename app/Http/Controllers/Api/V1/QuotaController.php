<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\QuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Mengambil statistik kuota dan breakdown tipe berkas pengguna aktif.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->quotaService->getQuotaStats($user);
        $breakdown = array_values($this->quotaService->getBreakdownByType($user));
        $largestFiles = $this->quotaService->getLargestFiles($user, 5)->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->original_name,
                'size' => $file->size,
                'formatted_size' => $this->quotaService->formatBytes($file->size),
                'mime_type' => $file->mime_type,
                'created_at' => $file->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'breakdown' => $breakdown,
                'largest_files' => $largestFiles,
            ],
        ]);
    }

    /**
     * Hitung ulang sinkronisasi kuota terpakai pengguna.
     */
    public function recalculate(Request $request): JsonResponse
    {
        $user = $request->user();
        $recalculatedBytes = $this->quotaService->recalculateUserUsage($user);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Kuota penyimpanan berhasil disinkronisasi.',
            'data' => $this->quotaService->getQuotaStats($user),
        ]);
    }
}
