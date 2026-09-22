<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class AdminService
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Mengambil statistik ringkasan sistem untuk dasbor admin.
     */
    public function getSystemStats(): array
    {
        $totalUsers = User::count();
        $academicUsers = User::where('account_type', 'academic')->count();
        $publicUsers = User::where('account_type', 'public')->count();
        $suspendedUsers = User::where('is_suspended', true)->count();

        $totalStorageAllocated = (int) User::sum('quota_bytes');
        $totalStorageUsed = (int) User::sum('used_bytes');
        $storagePercentage = $totalStorageAllocated > 0
            ? round(($totalStorageUsed / $totalStorageAllocated) * 100, 2)
            : 0.0;

        $totalFiles = File::count();
        $totalFolders = Folder::count();
        $totalShares = Share::where('is_active', true)->count();

        $uploadsTodayCount = File::whereDate('created_at', today())->count();
        $uploadsTodayBytes = (int) File::whereDate('created_at', today())->sum('size');

        $usersNearQuota = User::whereRaw('used_bytes >= (quota_bytes * 0.85)')
            ->orderByRaw('(used_bytes / quota_bytes) DESC')
            ->take(5)
            ->get();

        $recentActivities = ActivityLog::with('user')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return [
            'total_users' => $totalUsers,
            'academic_users' => $academicUsers,
            'public_users' => $publicUsers,
            'suspended_users' => $suspendedUsers,
            'total_storage_allocated' => $totalStorageAllocated,
            'total_storage_used' => $totalStorageUsed,
            'storage_percentage' => $storagePercentage,
            'formatted_total_allocated' => $this->quotaService->formatBytes($totalStorageAllocated),
            'formatted_total_used' => $this->quotaService->formatBytes($totalStorageUsed),
            'total_files' => $totalFiles,
            'total_folders' => $totalFolders,
            'total_shares' => $totalShares,
            'uploads_today_count' => $uploadsTodayCount,
            'uploads_today_bytes' => $uploadsTodayBytes,
            'formatted_uploads_today' => $this->quotaService->formatBytes($uploadsTodayBytes),
            'users_near_quota' => $usersNearQuota,
            'recent_activities' => $recentActivities,
        ];
    }

    /**
     * Membangun query daftar pengguna dengan filter.
     */
    public function getUsersQuery(array $filters = []): Builder
    {
        $query = User::with('roles');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['account_type']) && $filters['account_type'] !== 'all') {
            $query->where('account_type', $filters['account_type']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'suspended') {
                $query->where('is_suspended', true);
            } elseif ($filters['status'] === 'active') {
                $query->where('is_suspended', false);
            } elseif ($filters['status'] === 'near_quota') {
                $query->whereRaw('used_bytes >= (quota_bytes * 0.85)');
            }
        }

        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDirection = strtolower($filters['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Mengambil daftar pengguna terpaginasi.
     */
    public function getUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getUsersQuery($filters)->paginate($perPage);
    }

    /**
     * Menangguhkan atau mengaktifkan kembali akun pengguna.
     */
    public function toggleUserSuspension(User $targetUser, User $admin): bool
    {
        if ($targetUser->id === $admin->id) {
            throw new InvalidArgumentException('Administrator tidak dapat menangguhkan akunnya sendiri.');
        }

        if ($targetUser->hasRole('super-admin') && ! $admin->hasRole('super-admin')) {
            throw new InvalidArgumentException('Hanya Super Admin yang dapat mengubah status akun Super Admin lain.');
        }

        if ($targetUser->isSuspended()) {
            $targetUser->activate();

            ActivityLog::record(
                'user_activate',
                $targetUser,
                "Mengaktifkan kembali akun {$targetUser->name} ({$targetUser->email})",
                ['target_user_id' => $targetUser->id],
                $admin->id
            );

            return false;
        }

        $targetUser->suspend();

        ActivityLog::record(
            'user_suspend',
            $targetUser,
            "Menangguhkan akun {$targetUser->name} ({$targetUser->email})",
            ['target_user_id' => $targetUser->id],
            $admin->id
        );

        return true;
    }

    /**
     * Menetapkan batas kuota baru untuk pengguna (*override*).
     */
    public function overrideUserQuota(User $targetUser, int $newQuotaBytes, User $admin): User
    {
        if ($newQuotaBytes <= 0) {
            throw new InvalidArgumentException('Alokasi kuota harus lebih besar dari 0 byte.');
        }

        $oldQuota = $targetUser->quota_bytes;
        $targetUser->quota_bytes = $newQuotaBytes;
        $targetUser->save();

        ActivityLog::record(
            'user_quota_override',
            $targetUser,
            "Mengubah kuota {$targetUser->name} dari {$this->quotaService->formatBytes($oldQuota)} menjadi {$this->quotaService->formatBytes($newQuotaBytes)}",
            [
                'target_user_id' => $targetUser->id,
                'old_quota_bytes' => $oldQuota,
                'new_quota_bytes' => $newQuotaBytes,
            ],
            $admin->id
        );

        return $targetUser;
    }

    /**
     * Membangun query daftar riwayat log aktivitas dengan filter.
     */
    public function getActivityLogsQuery(array $filters = []): Builder
    {
        $query = ActivityLog::with('user');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['action']) && $filters['action'] !== 'all') {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Mengambil daftar log aktivitas terpaginasi.
     */
    public function getActivityLogs(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->getActivityLogsQuery($filters)->paginate($perPage);
    }
}
