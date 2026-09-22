<?php

namespace App\Observers;

use App\Models\File;

class FileObserver
{
    /**
     * Handle the File "created" event.
     */
    public function created(File $file): void
    {
        if ($file->size > 0 && $file->user) {
            $file->user->increment('used_bytes', $file->size);
        }
    }

    /**
     * Handle the File "updated" event.
     */
    public function updated(File $file): void
    {
        if ($file->wasChanged('size')) {
            $diff = $file->size - (int) $file->getOriginal('size');
            $user = $file->user;
            if ($user) {
                $user->used_bytes = max(0, $user->used_bytes + $diff);
                $user->save();
            }
        }
    }

    /**
     * Handle the File "force deleted" event.
     */
    public function forceDeleted(File $file): void
    {
        if ($file->size > 0) {
            $user = $file->user;
            if ($user) {
                $user->used_bytes = max(0, $user->used_bytes - $file->size);
                $user->save();
            }
        }
    }
}
