<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class FolderService
{
    /**
     * Membuat folder baru untuk pengguna.
     *
     * @throws ValidationException
     */
    public function create(User $user, string $name, ?int $parentId = null, ?string $color = null): Folder
    {
        $name = trim($name);

        if ($parentId !== null) {
            $parent = Folder::where('id', $parentId)
                ->where('user_id', $user->id)
                ->first();

            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Folder induk tidak ditemukan atau bukan milik Anda.'],
                ]);
            }
        }

        // Cek nama folder yang sama di lokasi yang sama
        $exists = Folder::where('user_id', $user->id)
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['Folder dengan nama "'.$name.'" sudah ada di lokasi ini.'],
            ]);
        }

        $folder = Folder::create([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'name' => $name,
            'color' => $color,
        ]);

        ActivityLog::record(
            'folder_create',
            $folder,
            'Membuat folder "'.$folder->name.'"',
            ['parent_id' => $parentId],
            $user->id
        );

        return $folder;
    }

    /**
     * Mengubah nama folder.
     *
     * @throws ValidationException
     */
    public function rename(Folder $folder, string $newName): Folder
    {
        $newName = trim($newName);

        if ($newName === $folder->name) {
            return $folder;
        }

        // Cek tabrakan nama di parent yang sama
        $exists = Folder::where('user_id', $folder->user_id)
            ->where('parent_id', $folder->parent_id)
            ->where('name', $newName)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['Folder dengan nama "'.$newName.'" sudah ada di lokasi ini.'],
            ]);
        }

        $oldName = $folder->name;
        $folder->update(['name' => $newName]);

        ActivityLog::record(
            'folder_rename',
            $folder,
            'Mengubah nama folder dari "'.$oldName.'" menjadi "'.$newName.'"',
            ['old_name' => $oldName, 'new_name' => $newName],
            $folder->user_id
        );

        return $folder;
    }

    /**
     * Menghapus folder secara soft-delete beserta seluruh isi di dalamnya (mematuhi rules.md §8).
     */
    public function delete(Folder $folder): bool
    {
        $this->softDeleteDescendants($folder);
        $folder->delete();

        ActivityLog::record(
            'folder_delete',
            $folder,
            'Memindahkan folder "'.$folder->name.'" ke tempat sampah',
            [],
            $folder->user_id
        );

        return true;
    }

    /**
     * Memulihkan folder dari tempat sampah beserta seluruh isi di dalamnya.
     */
    public function restore(Folder $folder): bool
    {
        $folder->restore();
        $this->restoreDescendants($folder);

        ActivityLog::record(
            'folder_restore',
            $folder,
            'Memulihkan folder "'.$folder->name.'" dari tempat sampah',
            [],
            $folder->user_id
        );

        return true;
    }

    /**
     * Menghapus folder secara permanen beserta seluruh file fisik dan subfolder di dalamnya.
     */
    public function permanentDelete(Folder $folder, ?FileService $fileService = null): bool
    {
        $fileService = $fileService ?? app(FileService::class);

        // Hapus permanen semua file (termasuk yang soft-deleted) di dalam folder ini
        $files = File::withTrashed()->where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            $fileService->permanentDelete($file);
        }

        // Hapus permanen semua subfolder secara rekursif
        $children = Folder::withTrashed()->where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $this->permanentDelete($child, $fileService);
        }

        $folderName = $folder->name;
        $userId = $folder->user_id;

        $folder->forceDelete();

        ActivityLog::record(
            'folder_force_delete',
            null,
            'Menghapus permanen folder "'.$folderName.'"',
            ['name' => $folderName],
            $userId
        );

        return true;
    }

    /**
     * Memulihkan seluruh subfolder dan file turunan secara rekursif.
     */
    protected function restoreDescendants(Folder $folder): void
    {
        File::onlyTrashed()->where('folder_id', $folder->id)->restore();

        $children = Folder::onlyTrashed()->where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $child->restore();
            $this->restoreDescendants($child);
        }
    }

    /**
     * Memindahkan folder ke folder induk baru.
     *
     * @throws ValidationException
     */
    public function move(Folder $folder, ?int $newParentId): Folder
    {
        if ($newParentId === $folder->parent_id) {
            return $folder;
        }

        if ($newParentId === $folder->id) {
            throw ValidationException::withMessages([
                'target_parent_id' => ['Folder tidak dapat dipindahkan ke dalam dirinya sendiri.'],
            ]);
        }

        if ($newParentId !== null) {
            $targetParent = Folder::where('id', $newParentId)
                ->where('user_id', $folder->user_id)
                ->first();

            if (! $targetParent) {
                throw ValidationException::withMessages([
                    'target_parent_id' => ['Folder tujuan tidak ditemukan atau bukan milik Anda.'],
                ]);
            }

            // Cegah siklus sirkular: folder tujuan tidak boleh merupakan keturunan dari folder ini
            if ($this->isDescendantOf($targetParent, $folder)) {
                throw ValidationException::withMessages([
                    'target_parent_id' => ['Folder tidak dapat dipindahkan ke dalam subfoldernya sendiri.'],
                ]);
            }
        }

        // Cek tabrakan nama di lokasi baru
        $exists = Folder::where('user_id', $folder->user_id)
            ->where('parent_id', $newParentId)
            ->where('name', $folder->name)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'target_parent_id' => ['Folder dengan nama "'.$folder->name.'" sudah ada di lokasi tujuan.'],
            ]);
        }

        $oldParentId = $folder->parent_id;
        $folder->update(['parent_id' => $newParentId]);

        ActivityLog::record(
            'folder_move',
            $folder,
            'Memindahkan folder "'.$folder->name.'"',
            ['old_parent_id' => $oldParentId, 'new_parent_id' => $newParentId],
            $folder->user_id
        );

        return $folder;
    }

    /**
     * Mengambil daftar isi folder (subfolder dan file) beserta breadcrumbs.
     *
     * @return array{
     *     current_folder: ?Folder,
     *     breadcrumbs: array<int, array{id: ?int, name: string}>,
     *     folders: Collection<int, Folder>,
     *     files: Collection<int, File>
     * }
     */
    public function listContents(User $user, ?int $parentId = null, ?string $search = null): array
    {
        $currentFolder = null;
        $breadcrumbs = [
            ['id' => null, 'name' => 'Semua File'],
        ];

        if ($parentId !== null) {
            $currentFolder = Folder::where('id', $parentId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            foreach ($currentFolder->getBreadcrumbs() as $crumb) {
                $breadcrumbs[] = $crumb;
            }
        }

        $folderQuery = Folder::where('user_id', $user->id)
            ->where('parent_id', $parentId);

        $fileQuery = File::where('user_id', $user->id)
            ->where('folder_id', $parentId);

        if (! empty($search)) {
            $folderQuery->where('name', 'like', '%'.$search.'%');
            $fileQuery->where('original_name', 'like', '%'.$search.'%');
        }

        $folders = $folderQuery->withCount(['files', 'children'])->orderBy('name')->get();
        $files = $fileQuery->orderBy('created_at', 'desc')->get();

        return [
            'current_folder' => $currentFolder,
            'breadcrumbs' => $breadcrumbs,
            'folders' => $folders,
            'files' => $files,
        ];
    }

    /**
     * Mengambil pohon hierarki folder untuk dialog navigasi atau pemindahan.
     *
     * @return array<int, array{id: int, name: string, parent_id: ?int, children: array}>
     */
    public function getTree(User $user, ?int $excludeId = null): array
    {
        $allFolders = Folder::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return $this->buildTree($allFolders, null, $excludeId);
    }

    /**
     * Cek apakah $candidate adalah turunan/anak dari $ancestor.
     */
    public function isDescendantOf(Folder $candidate, Folder $ancestor): bool
    {
        $current = $candidate;

        while ($current->parent_id !== null) {
            if ($current->parent_id === $ancestor->id) {
                return true;
            }
            $current = $current->parent;
            if (! $current) {
                break;
            }
        }

        return false;
    }

    /**
     * Menghapus seluruh subfolder dan file turunan secara rekursif (soft-delete).
     */
    protected function softDeleteDescendants(Folder $folder): void
    {
        // Soft delete seluruh file di dalam folder ini
        File::where('folder_id', $folder->id)->delete();

        // Soft delete seluruh anak folder secara rekursif
        $children = Folder::where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $this->softDeleteDescendants($child);
            $child->delete();
        }
    }

    /**
     * Membangun array hierarki pohon dari kumpulan folder.
     *
     * @param  Collection<int, Folder>  $folders
     * @return array<int, array{id: int, name: string, parent_id: ?int, children: array}>
     */
    protected function buildTree(Collection $folders, ?int $parentId = null, ?int $excludeId = null): array
    {
        $branch = [];

        foreach ($folders as $folder) {
            if ($folder->id === $excludeId) {
                continue;
            }

            if ($folder->parent_id === $parentId) {
                $children = $this->buildTree($folders, $folder->id, $excludeId);
                $branch[] = [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'parent_id' => $folder->parent_id,
                    'color' => $folder->color,
                    'children' => $children,
                ];
            }
        }

        return $branch;
    }
}
