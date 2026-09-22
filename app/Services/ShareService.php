<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShareService
{
    /**
     * Membuat atau mengaktifkan kembali tautan berbagi publik untuk berkas atau folder.
     *
     * @throws ValidationException
     */
    public function createPublicShare(Model $shareable, User $user, string $permission = 'view', ?int $expiryDays = null): Share
    {
        $this->ensureShareableBelongsToUser($shareable, $user);

        if (! in_array($permission, ['view', 'download'], true)) {
            $permission = 'view';
        }

        $defaultDays = config('cloudcampus.share_link_default_expiry_days', 7);
        $days = $expiryDays ?? $defaultDays;
        $expiresAt = $days > 0 ? now()->addDays($days) : null;

        // Cari tautan publik yang sudah ada sebelumnya untuk item ini
        $share = Share::where('user_id', $user->id)
            ->where('shareable_type', get_class($shareable))
            ->where('shareable_id', $shareable->id)
            ->whereNull('shared_with_user_id')
            ->first();

        if ($share) {
            $share->update([
                'permission' => $permission,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);
        } else {
            $share = Share::create([
                'user_id' => $user->id,
                'shareable_type' => get_class($shareable),
                'shareable_id' => $shareable->id,
                'token' => Str::random(64),
                'permission' => $permission,
                'expires_at' => $expiresAt,
                'shared_with_user_id' => null,
                'is_active' => true,
            ]);
        }

        $itemName = $this->getItemName($shareable);

        ActivityLog::record(
            'share_create_public',
            $shareable,
            "Membuat tautan berbagi publik untuk \"{$itemName}\"",
            [
                'share_id' => $share->id,
                'token' => $share->token,
                'permission' => $permission,
                'expires_at' => $expiresAt?->toIso8601String(),
            ],
            $user->id
        );

        return $share;
    }

    /**
     * Membagikan berkas atau folder secara privat kepada pengguna terdaftar tertentu.
     *
     * @throws ValidationException
     */
    public function shareWithUser(Model $shareable, User $owner, User $recipient, string $permission = 'view', ?int $expiryDays = null): Share
    {
        $this->ensureShareableBelongsToUser($shareable, $owner);

        if ($owner->id === $recipient->id) {
            throw ValidationException::withMessages([
                'recipient' => ['Anda tidak dapat membagikan berkas kepada diri Anda sendiri.'],
            ]);
        }

        if (! in_array($permission, ['view', 'download'], true)) {
            $permission = 'view';
        }

        $defaultDays = config('cloudcampus.share_link_default_expiry_days', 7);
        $days = $expiryDays ?? $defaultDays;
        $expiresAt = $days > 0 ? now()->addDays($days) : null;

        $share = Share::updateOrCreate(
            [
                'user_id' => $owner->id,
                'shareable_type' => get_class($shareable),
                'shareable_id' => $shareable->id,
                'shared_with_user_id' => $recipient->id,
            ],
            [
                'permission' => $permission,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]
        );

        $itemName = $this->getItemName($shareable);

        ActivityLog::record(
            'share_with_user',
            $shareable,
            "Membagikan \"{$itemName}\" kepada {$recipient->name} ({$recipient->email})",
            [
                'share_id' => $share->id,
                'recipient_id' => $recipient->id,
                'permission' => $permission,
                'expires_at' => $expiresAt?->toIso8601String(),
            ],
            $owner->id
        );

        return $share;
    }

    /**
     * Memperbarui pengaturan tautan berbagi.
     */
    public function updateShare(Share $share, array $data): Share
    {
        if (isset($data['permission']) && ! in_array($data['permission'], ['view', 'download'], true)) {
            unset($data['permission']);
        }

        $share->update($data);

        ActivityLog::record(
            'share_update',
            $share->shareable,
            "Memperbarui tautan berbagi #{$share->id}",
            $data,
            $share->user_id
        );

        return $share;
    }

    /**
     * Mencabut / menonaktifkan tautan berbagi.
     */
    public function revokeShare(Share $share): bool
    {
        $share->update(['is_active' => false]);

        ActivityLog::record(
            'share_revoke',
            $share->shareable,
            "Menonaktifkan tautan berbagi #{$share->id}",
            ['token' => $share->token],
            $share->user_id
        );

        return true;
    }

    /**
     * Menghapus record tautan berbagi secara permanen.
     */
    public function deleteShare(Share $share): bool
    {
        $shareId = $share->id;
        $userId = $share->user_id;

        $share->delete();

        ActivityLog::record(
            'share_delete',
            null,
            "Menghapus tautan berbagi #{$shareId}",
            [],
            $userId
        );

        return true;
    }

    /**
     * Memvalidasi akses berdasarkan token berbagi publik atau privat.
     */
    public function validateAccess(string $token, ?User $user = null): Share
    {
        $share = Share::where('token', $token)
            ->where('is_active', true)
            ->with(['shareable', 'user'])
            ->first();

        if (! $share || ! $share->shareable) {
            abort(404, 'Tautan berbagi tidak ditemukan atau telah dinonaktifkan.');
        }

        if ($share->isExpired()) {
            abort(410, 'Masa berlaku tautan berbagi ini telah kedaluwarsa.');
        }

        // Jika dibagikan secara privat ke pengguna tertentu
        if ($share->shared_with_user_id !== null) {
            if ($user === null) {
                abort(401, 'Silakan masuk dengan akun yang berhak untuk mengakses berkas ini.');
            }

            if ($user->id !== $share->shared_with_user_id && $user->id !== $share->user_id && ! $user->hasRole('super-admin')) {
                abort(403, 'Akun Anda tidak memiliki izin untuk mengakses berkas yang dibagikan ini.');
            }
        }

        return $share;
    }

    /**
     * Mengambil daftar tautan berbagi yang dibuat oleh pengguna.
     */
    public function getSharedByMe(User $user)
    {
        return Share::where('user_id', $user->id)
            ->with(['shareable', 'sharedWithUser'])
            ->latest()
            ->get();
    }

    /**
     * Mengambil daftar berkas/folder yang dibagikan kepada pengguna.
     */
    public function getSharedWithMe(User $user)
    {
        return Share::where('shared_with_user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['shareable', 'user'])
            ->latest()
            ->get();
    }

    /**
     * Memastikan item yang dibagikan adalah milik pengguna yang bersangkutan.
     *
     * @throws ValidationException
     */
    protected function ensureShareableBelongsToUser(Model $shareable, User $user): void
    {
        if ($shareable->user_id !== $user->id && ! $user->hasRole('super-admin')) {
            throw ValidationException::withMessages([
                'shareable' => ['Anda hanya dapat membagikan berkas atau folder milik Anda sendiri.'],
            ]);
        }
    }

    /**
     * Mengambil nama representatif dari model shareable.
     */
    protected function getItemName(Model $shareable): string
    {
        if ($shareable instanceof File) {
            return $shareable->original_name;
        }

        if ($shareable instanceof Folder) {
            return $shareable->name;
        }

        return 'Item';
    }
}
