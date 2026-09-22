<?php

namespace App\Policies;

use App\Models\Share;
use App\Models\User;

class SharePolicy
{
    /**
     * Menentukan apakah user dapat melihat detail share.
     */
    public function view(User $user, Share $share): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $share->user_id || $user->id === $share->shared_with_user_id;
    }

    /**
     * Menentukan apakah user dapat memperbarui tautan share.
     */
    public function update(User $user, Share $share): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $share->user_id;
    }

    /**
     * Menentukan apakah user dapat menghapus / mencabut tautan share.
     */
    public function delete(User $user, Share $share): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $share->user_id;
    }
}
