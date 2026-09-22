<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Collection;

class QuotaService
{
    /**
     * Dapatkan ringkasan statistik kuota pengguna lengkap.
     */
    public function getQuotaStats(User $user): array
    {
        $quotaBytes = (int) $user->quota_bytes;
        $usedBytes = (int) $user->used_bytes;
        $remainingBytes = max(0, $quotaBytes - $usedBytes);
        $percentage = $user->quotaUsagePercentage();

        $trashBytes = $this->getTrashUsage($user);
        $activeFileCount = File::where('user_id', $user->id)->count();
        $trashedFileCount = File::onlyTrashed()->where('user_id', $user->id)->count();
        $folderCount = Folder::where('user_id', $user->id)->count();

        return [
            'quota_bytes' => $quotaBytes,
            'used_bytes' => $usedBytes,
            'remaining_bytes' => $remainingBytes,
            'percentage' => $percentage,
            'is_warning' => $percentage >= 90.0,
            'is_critical' => $percentage >= 98.0,
            'formatted_quota' => $this->formatBytes($quotaBytes),
            'formatted_used' => $this->formatBytes($usedBytes),
            'formatted_remaining' => $this->formatBytes($remainingBytes),
            'trash_bytes' => $trashBytes,
            'formatted_trash' => $this->formatBytes($trashBytes),
            'active_file_count' => $activeFileCount,
            'trashed_file_count' => $trashedFileCount,
            'folder_count' => $folderCount,
            'total_files' => $activeFileCount + $trashedFileCount,
        ];
    }

    /**
     * Dapatkan pengelompokan penggunaan kuota berdasarkan jenis berkas.
     */
    public function getBreakdownByType(User $user): array
    {
        // Ambil semua berkas milik user (termasuk trashed karena masih memakan kuota disk)
        $files = File::withTrashed()
            ->where('user_id', $user->id)
            ->get(['mime_type', 'size']);

        $totalUsed = max(1, (int) $user->used_bytes);

        $categories = [
            'documents' => [
                'key' => 'documents',
                'label' => 'Dokumen',
                'color' => '#3b82f6', // blue-500
                'bg_class' => 'bg-blue-500',
                'text_class' => 'text-blue-700',
                'bg_light' => 'bg-blue-50',
                'bytes' => 0,
                'count' => 0,
            ],
            'images' => [
                'key' => 'images',
                'label' => 'Gambar',
                'color' => '#8b5cf6', // violet-500
                'bg_class' => 'bg-violet-500',
                'text_class' => 'text-violet-700',
                'bg_light' => 'bg-violet-50',
                'bytes' => 0,
                'count' => 0,
            ],
            'videos' => [
                'key' => 'videos',
                'label' => 'Video',
                'color' => '#ef4444', // red-500
                'bg_class' => 'bg-red-500',
                'text_class' => 'text-red-700',
                'bg_light' => 'bg-red-50',
                'bytes' => 0,
                'count' => 0,
            ],
            'audio' => [
                'key' => 'audio',
                'label' => 'Audio',
                'color' => '#f59e0b', // amber-500
                'bg_class' => 'bg-amber-500',
                'text_class' => 'text-amber-700',
                'bg_light' => 'bg-amber-50',
                'bytes' => 0,
                'count' => 0,
            ],
            'archives' => [
                'key' => 'archives',
                'label' => 'Arsip & ZIP',
                'color' => '#10b981', // emerald-500
                'bg_class' => 'bg-emerald-500',
                'text_class' => 'text-emerald-700',
                'bg_light' => 'bg-emerald-50',
                'bytes' => 0,
                'count' => 0,
            ],
            'others' => [
                'key' => 'others',
                'label' => 'Lainnya',
                'color' => '#94a3b8', // slate-400
                'bg_class' => 'bg-slate-400',
                'text_class' => 'text-slate-700',
                'bg_light' => 'bg-slate-100',
                'bytes' => 0,
                'count' => 0,
            ],
        ];

        foreach ($files as $file) {
            $categoryKey = $this->determineCategory($file->mime_type);
            $categories[$categoryKey]['bytes'] += (int) $file->size;
            $categories[$categoryKey]['count']++;
        }

        // Hitung persentase dan format ukuran
        foreach ($categories as $key => &$cat) {
            $cat['percentage'] = round(($cat['bytes'] / $totalUsed) * 100, 1);
            $cat['formatted'] = $this->formatBytes($cat['bytes']);
        }
        unset($cat);

        return $categories;
    }

    /**
     * Hitung total kuota yang terikat pada berkas di Tempat Sampah.
     */
    public function getTrashUsage(User $user): int
    {
        return (int) File::onlyTrashed()
            ->where('user_id', $user->id)
            ->sum('size');
    }

    /**
     * Ambil berkas-berkas terbesar milik pengguna.
     */
    public function getLargestFiles(User $user, int $limit = 5): Collection
    {
        return File::where('user_id', $user->id)
            ->with('folder')
            ->orderByDesc('size')
            ->take($limit)
            ->get();
    }

    /**
     * Hitung ulang total byte yang dimiliki pengguna secara aktual dan mutakhirkan used_bytes.
     */
    public function recalculateUserUsage(User $user): int
    {
        $actualBytes = (int) File::withTrashed()
            ->where('user_id', $user->id)
            ->sum('size');

        $user->used_bytes = $actualBytes;
        $user->save();

        return $actualBytes;
    }

    /**
     * Menentukan kategori berdasarkan MIME type.
     */
    public function determineCategory(?string $mime): string
    {
        if (empty($mime)) {
            return 'others';
        }

        $mime = strtolower($mime);

        if (str_starts_with($mime, 'image/')) {
            return 'images';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'videos';
        }

        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        if (
            str_contains($mime, 'pdf') ||
            str_contains($mime, 'word') ||
            str_contains($mime, 'document') ||
            str_contains($mime, 'sheet') ||
            str_contains($mime, 'excel') ||
            str_contains($mime, 'presentation') ||
            str_contains($mime, 'powerpoint') ||
            str_contains($mime, 'text/') ||
            str_contains($mime, 'rtf')
        ) {
            return 'documents';
        }

        if (
            str_contains($mime, 'zip') ||
            str_contains($mime, 'tar') ||
            str_contains($mime, 'compressed') ||
            str_contains($mime, 'gzip') ||
            str_contains($mime, 'rar') ||
            str_contains($mime, '7z')
        ) {
            return 'archives';
        }

        return 'others';
    }

    /**
     * Format angka byte menjadi representasi teks manusia (B, KB, MB, GB, TB).
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        $value = $bytes / pow(1024, $power);

        return round($value, $precision).' '.$units[$power];
    }
}
