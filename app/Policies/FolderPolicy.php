<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    /**
     * Tentukan apakah user dapat melihat folder ini.
     */
    public function view(User $user, Folder $folder): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $folder->user_id;
    }

    /**
     * Tentukan apakah user dapat membuat folder.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('folders.create');
    }

    /**
     * Tentukan apakah user dapat memperbarui / mengubah nama folder.
     */
    public function update(User $user, Folder $folder): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $folder->user_id;
    }

    /**
     * Tentukan apakah user dapat menghapus folder.
     */
    public function delete(User $user, Folder $folder): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $folder->user_id;
    }

    /**
     * Tentukan apakah user dapat memulihkan folder dari tempat sampah.
     */
    public function restore(User $user, Folder $folder): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $folder->user_id;
    }

    /**
     * Tentukan apakah user dapat menghapus permanen folder.
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $folder->user_id;
    }
}
