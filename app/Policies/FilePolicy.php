<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat metadata berkas.
     */
    public function view(User $user, File $file): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('admin-kampus')) {
            return true;
        }

        return $user->id === $file->user_id;
    }

    /**
     * Tentukan apakah pengguna dapat mengunduh berkas fisik.
     */
    public function download(User $user, File $file): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('admin-kampus')) {
            return true;
        }

        return $user->id === $file->user_id;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui atau mengubah nama berkas.
     */
    public function update(User $user, File $file): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $file->user_id;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus berkas (soft-delete).
     */
    public function delete(User $user, File $file): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $file->user_id;
    }

    /**
     * Tentukan apakah pengguna dapat memulihkan berkas dari tempat sampah.
     */
    public function restore(User $user, File $file): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $file->user_id;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus berkas secara permanen.
     */
    public function forceDelete(User $user, File $file): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->id === $file->user_id;
    }
}
