<?php

namespace App\Livewire;

use App\Models\File;
use App\Models\Folder;
use App\Services\FileService;
use App\Services\FolderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileExplorer extends Component
{
    use WithFileUploads;

    public ?int $currentFolderId = null;

    public string $search = '';

    public string $viewMode = 'grid'; // 'grid' | 'list'

    // Multi-file Upload State
    public $uploads = [];

    public bool $showUploadModal = false;

    // Form State untuk Modal Buat Folder
    public bool $showCreateModal = false;

    public string $newFolderName = '';

    public ?string $newFolderColor = '#3B82F6';

    // Form State untuk Modal Ubah Nama Folder
    public bool $showRenameModal = false;

    public ?int $folderToRenameId = null;

    public string $editFolderName = '';

    // Form State untuk Modal Ubah Nama Berkas
    public bool $showFileRenameModal = false;

    public ?int $fileToRenameId = null;

    public string $editFileName = '';

    // Form State untuk Modal Pindah Folder
    public bool $showMoveModal = false;

    public ?int $folderToMoveId = null;

    public ?int $targetParentId = null;

    // Form State untuk Modal Pindah Berkas
    public bool $showFileMoveModal = false;

    public ?int $fileToMoveId = null;

    public ?int $targetFileFolderId = null;

    // Form State untuk Modal Konfirmasi Hapus Folder
    public bool $showDeleteModal = false;

    public ?int $folderToDeleteId = null;

    public string $folderToDeleteName = '';

    // Form State untuk Modal Konfirmasi Hapus Berkas
    public bool $showDeleteFileModal = false;

    public ?int $fileToDeleteId = null;

    public string $fileToDeleteName = '';

    // Pesan Notifikasi (Toast / Banner)
    public ?string $feedbackMessage = null;

    public ?string $feedbackType = 'success'; // 'success' | 'error'

    protected $queryString = [
        'currentFolderId' => ['except' => null, 'as' => 'folder'],
        'search' => ['except' => ''],
    ];

    public function navigateToFolder(?int $folderId = null): void
    {
        $this->currentFolderId = $folderId;
        $this->resetModals();
    }

    public function toggleViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'list'], true)) {
            $this->viewMode = $mode;
        }
    }

    // --- UPLOAD METHODS ---
    public function openUploadModal(): void
    {
        $this->resetModals();
        $this->uploads = [];
        $this->showUploadModal = true;
    }

    public function updatedUploads(): void
    {
        $this->validate([
            'uploads.*' => ['required', 'file', 'max:'.config('cloudcampus.max_upload_size_kb', 102400)],
        ], [
            'uploads.*.max' => 'Ukuran salah satu berkas melebihi batas maksimal sistem.',
        ]);
    }

    public function uploadFiles(FileService $fileService): void
    {
        $this->validate([
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => ['file', 'max:'.config('cloudcampus.max_upload_size_kb', 102400)],
        ]);

        $successCount = 0;
        $errors = [];

        foreach ($this->uploads as $upload) {
            try {
                $fileService->upload(
                    Auth::user(),
                    $upload,
                    $this->currentFolderId
                );
                $successCount++;
            } catch (ValidationException $e) {
                $errors[] = $upload->getClientOriginalName().': '.implode(', ', $e->validator->errors()->all());
            } catch (\Throwable $e) {
                $errors[] = $upload->getClientOriginalName().': '.$e->getMessage();
            }
        }

        $this->uploads = [];
        $this->showUploadModal = false;

        if ($successCount > 0) {
            $this->feedbackType = empty($errors) ? 'success' : 'error';
            $this->feedbackMessage = "Berhasil mengunggah {$successCount} berkas.".(empty($errors) ? '' : ' Namun terjadi kesalahan: '.implode('; ', $errors));
        } else {
            $this->feedbackType = 'error';
            $this->feedbackMessage = 'Gagal mengunggah berkas: '.implode('; ', $errors);
        }
    }

    // --- FOLDER METHODS ---
    public function openCreateModal(): void
    {
        $this->resetModals();
        $this->newFolderName = '';
        $this->newFolderColor = '#3B82F6';
        $this->showCreateModal = true;
    }

    public function createFolder(FolderService $folderService): void
    {
        $this->validate([
            'newFolderName' => ['required', 'string', 'max:255'],
        ], [
            'newFolderName.required' => 'Nama folder wajib diisi.',
            'newFolderName.max' => 'Nama folder maksimal 255 karakter.',
        ]);

        try {
            $folderService->create(
                Auth::user(),
                $this->newFolderName,
                $this->currentFolderId,
                $this->newFolderColor
            );

            $this->showCreateModal = false;
            $this->feedbackType = 'success';
            $this->feedbackMessage = 'Folder "'.$this->newFolderName.'" berhasil dibuat.';
        } catch (ValidationException $e) {
            $this->addError('newFolderName', $e->validator->errors()->first('name') ?: $e->getMessage());
        }
    }

    public function openRenameModal(int $folderId): void
    {
        $this->resetModals();
        $folder = Folder::where('id', $folderId)->where('user_id', Auth::id())->firstOrFail();

        $this->folderToRenameId = $folder->id;
        $this->editFolderName = $folder->name;
        $this->showRenameModal = true;
    }

    public function renameFolder(FolderService $folderService): void
    {
        $this->validate([
            'editFolderName' => ['required', 'string', 'max:255'],
        ]);

        $folder = Folder::where('id', $this->folderToRenameId)->where('user_id', Auth::id())->firstOrFail();

        try {
            $folderService->rename($folder, $this->editFolderName);

            $this->showRenameModal = false;
            $this->feedbackType = 'success';
            $this->feedbackMessage = 'Nama folder berhasil diubah.';
        } catch (ValidationException $e) {
            $this->addError('editFolderName', $e->validator->errors()->first('name') ?: $e->getMessage());
        }
    }

    public function openMoveModal(int $folderId): void
    {
        $this->resetModals();
        $folder = Folder::where('id', $folderId)->where('user_id', Auth::id())->firstOrFail();

        $this->folderToMoveId = $folder->id;
        $this->targetParentId = $folder->parent_id;
        $this->showMoveModal = true;
    }

    public function moveFolder(FolderService $folderService): void
    {
        $folder = Folder::where('id', $this->folderToMoveId)->where('user_id', Auth::id())->firstOrFail();

        try {
            $folderService->move($folder, $this->targetParentId);

            $this->showMoveModal = false;
            $this->feedbackType = 'success';
            $this->feedbackMessage = 'Folder "'.$folder->name.'" berhasil dipindahkan.';
        } catch (ValidationException $e) {
            $this->addError('targetParentId', $e->validator->errors()->first('target_parent_id') ?: $e->getMessage());
        }
    }

    public function confirmDelete(int $folderId): void
    {
        $this->resetModals();
        $folder = Folder::where('id', $folderId)->where('user_id', Auth::id())->firstOrFail();

        $this->folderToDeleteId = $folder->id;
        $this->folderToDeleteName = $folder->name;
        $this->showDeleteModal = true;
    }

    public function deleteFolder(FolderService $folderService): void
    {
        if ($this->folderToDeleteId) {
            $folder = Folder::where('id', $this->folderToDeleteId)->where('user_id', Auth::id())->firstOrFail();
            $name = $folder->name;

            $folderService->delete($folder);

            $this->showDeleteModal = false;
            $this->feedbackType = 'success';
            $this->feedbackMessage = 'Folder "'.$name.'" berhasil dipindahkan ke tempat sampah.';
        }
    }

    // --- FILE METHODS ---
    public function openFileRenameModal(int $fileId): void
    {
        $this->resetModals();
        $file = File::where('id', $fileId)->where('user_id', Auth::id())->firstOrFail();

        $this->fileToRenameId = $file->id;
        $this->editFileName = $file->original_name;
        $this->showFileRenameModal = true;
    }

    public function renameFile(FileService $fileService): void
    {
        $this->validate([
            'editFileName' => ['required', 'string', 'max:255'],
        ]);

        $file = File::where('id', $this->fileToRenameId)->where('user_id', Auth::id())->firstOrFail();

        $fileService->rename($file, $this->editFileName);

        $this->showFileRenameModal = false;
        $this->feedbackType = 'success';
        $this->feedbackMessage = 'Nama berkas berhasil diubah.';
    }

    public function openFileMoveModal(int $fileId): void
    {
        $this->resetModals();
        $file = File::where('id', $fileId)->where('user_id', Auth::id())->firstOrFail();

        $this->fileToMoveId = $file->id;
        $this->targetFileFolderId = $file->folder_id;
        $this->showFileMoveModal = true;
    }

    public function moveFile(FileService $fileService): void
    {
        $file = File::where('id', $this->fileToMoveId)->where('user_id', Auth::id())->firstOrFail();

        try {
            $fileService->move($file, $this->targetFileFolderId);

            $this->showFileMoveModal = false;
            $this->feedbackType = 'success';
            $this->feedbackMessage = 'Berkas "'.$file->original_name.'" berhasil dipindahkan.';
        } catch (ValidationException $e) {
            $this->addError('targetFileFolderId', $e->validator->errors()->first('folder_id') ?: $e->getMessage());
        }
    }

    public function confirmDeleteFile(int $fileId): void
    {
        $this->resetModals();
        $file = File::where('id', $fileId)->where('user_id', Auth::id())->firstOrFail();

        $this->fileToDeleteId = $file->id;
        $this->fileToDeleteName = $file->original_name;
        $this->showDeleteFileModal = true;
    }

    public function deleteFile(FileService $fileService): void
    {
        if ($this->fileToDeleteId) {
            $file = File::where('id', $this->fileToDeleteId)->where('user_id', Auth::id())->firstOrFail();
            $name = $file->original_name;

            $fileService->delete($file);

            $this->showDeleteFileModal = false;
            $this->feedbackType = 'success';
            $this->feedbackMessage = 'Berkas "'.$name.'" berhasil dipindahkan ke tempat sampah.';
        }
    }

    public function toggleFavorite(int $fileId): void
    {
        $file = File::where('id', $fileId)->where('user_id', Auth::id())->firstOrFail();
        $file->update(['is_favorite' => ! $file->is_favorite]);
    }

    public function resetModals(): void
    {
        $this->showCreateModal = false;
        $this->showRenameModal = false;
        $this->showMoveModal = false;
        $this->showDeleteModal = false;
        $this->showUploadModal = false;
        $this->showFileRenameModal = false;
        $this->showFileMoveModal = false;
        $this->showDeleteFileModal = false;
        $this->resetErrorBag();
    }

    public function render(FolderService $folderService): View
    {
        $contents = $folderService->listContents(
            Auth::user(),
            $this->currentFolderId,
            $this->search
        );

        $folderTree = ($this->showMoveModal || $this->showFileMoveModal)
            ? $folderService->getTree(Auth::user(), $this->folderToMoveId)
            : [];

        return view('livewire.file-explorer', [
            'currentFolder' => $contents['current_folder'],
            'breadcrumbs' => $contents['breadcrumbs'],
            'folders' => $contents['folders'],
            'files' => $contents['files'],
            'folderTree' => $folderTree,
        ]);
    }
}
