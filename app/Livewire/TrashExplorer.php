<?php

namespace App\Livewire;

use App\Models\File;
use App\Models\Folder;
use App\Services\FileService;
use App\Services\FolderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrashExplorer extends Component
{
    public string $search = '';
    public string $filter = 'all'; // 'all', 'folders', 'files'

    public function restoreFile(int $fileId, FileService $fileService): void
    {
        $file = File::onlyTrashed()
            ->where('id', $fileId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->authorize('restore', $file);

        $fileService->restore($file);

        session()->flash('message', "Berkas \"{$file->original_name}\" berhasil dipulihkan.");
    }

    public function forceDeleteFile(int $fileId, FileService $fileService): void
    {
        $file = File::onlyTrashed()
            ->where('id', $fileId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->authorize('forceDelete', $file);

        $fileName = $file->original_name;
        $fileService->permanentDelete($file);

        session()->flash('message', "Berkas \"{$fileName}\" telah dihapus secara permanen.");
    }

    public function restoreFolder(int $folderId, FolderService $folderService): void
    {
        $folder = Folder::onlyTrashed()
            ->where('id', $folderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->authorize('restore', $folder);

        $folderService->restore($folder);

        session()->flash('message', "Folder \"{$folder->name}\" berhasil dipulihkan.");
    }

    public function forceDeleteFolder(int $folderId, FolderService $folderService): void
    {
        $folder = Folder::onlyTrashed()
            ->where('id', $folderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->authorize('forceDelete', $folder);

        $folderName = $folder->name;
        $folderService->permanentDelete($folder);

        session()->flash('message', "Folder \"{$folderName}\" beserta isinya telah dihapus permanen.");
    }

    public function emptyTrash(FileService $fileService, FolderService $folderService): void
    {
        $userId = Auth::id();

        // Hapus permanen semua folder sampah milik user
        $folders = Folder::onlyTrashed()->where('user_id', $userId)->get();
        foreach ($folders as $folder) {
            $folderService->permanentDelete($folder, $fileService);
        }

        // Hapus permanen sisa berkas sampah milik user
        $files = File::onlyTrashed()->where('user_id', $userId)->get();
        foreach ($files as $file) {
            $fileService->permanentDelete($file);
        }

        session()->flash('message', 'Tempat sampah berhasil dikosongkan.');
    }

    public function render()
    {
        $userId = Auth::id();

        $folderQuery = Folder::onlyTrashed()->where('user_id', $userId);
        $fileQuery = File::onlyTrashed()->where('user_id', $userId);

        if (! empty($this->search)) {
            $folderQuery->where('name', 'like', '%' . $this->search . '%');
            $fileQuery->where('original_name', 'like', '%' . $this->search . '%');
        }

        $folders = ($this->filter === 'files') ? collect() : $folderQuery->orderBy('deleted_at', 'desc')->get();
        $files = ($this->filter === 'folders') ? collect() : $fileQuery->orderBy('deleted_at', 'desc')->get();

        return view('livewire.trash-explorer', [
            'folders' => $folders,
            'files' => $files,
            'totalCount' => $folderQuery->count() + $fileQuery->count(),
        ]);
    }
}
