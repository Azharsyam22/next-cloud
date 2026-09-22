<?php

namespace App\Livewire;

use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Models\User;
use App\Services\ShareService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ShareModal extends Component
{
    public bool $isOpen = false;
    public string $type = 'file'; // 'file' atau 'folder'
    public int $itemId = 0;
    public string $itemName = '';
    public string $activeTab = 'link'; // 'link' atau 'people'

    // Tab 1: Tautan Publik
    public bool $isPublicActive = false;
    public string $publicLinkUrl = '';
    public string $publicPermission = 'view'; // 'view' atau 'download'
    public int $publicExpiryDays = 7;
    public ?int $publicShareId = null;

    // Tab 2: Bagikan ke Orang
    public string $userSearch = '';
    public string $recipientPermission = 'view';
    public int $recipientExpiryDays = 7;

    #[On('open-share-modal')]
    public function openModal(string $type, int $id): void
    {
        $this->type = $type;
        $this->itemId = $id;
        $this->activeTab = 'link';
        $this->userSearch = '';

        $item = $this->resolveItem();
        if (! $item || ($item->user_id !== Auth::id() && ! Auth::user()->hasRole('super-admin'))) {
            return;
        }

        $this->itemName = $type === 'file' ? $item->original_name : $item->name;

        // Ambil data tautan publik jika ada
        $publicShare = Share::where('user_id', Auth::id())
            ->where('shareable_type', get_class($item))
            ->where('shareable_id', $item->id)
            ->whereNull('shared_with_user_id')
            ->first();

        if ($publicShare && $publicShare->is_active) {
            $this->isPublicActive = true;
            $this->publicShareId = $publicShare->id;
            $this->publicPermission = $publicShare->permission;
            $this->publicLinkUrl = url("/s/{$publicShare->token}");
        } else {
            $this->isPublicActive = false;
            $this->publicShareId = null;
            $this->publicPermission = 'view';
            $this->publicLinkUrl = '';
        }

        $this->isOpen = true;
    }

    public function togglePublicShare(ShareService $shareService): void
    {
        $item = $this->resolveItem();
        if (! $item) {
            return;
        }

        if ($this->isPublicActive) {
            // Aktifkan atau perbarui
            $share = $shareService->createPublicShare(
                $item,
                Auth::user(),
                $this->publicPermission,
                $this->publicExpiryDays > 0 ? $this->publicExpiryDays : null
            );

            $this->publicShareId = $share->id;
            $this->publicLinkUrl = url("/s/{$share->token}");
            session()->flash('message', 'Tautan berbagi publik berhasil diaktifkan.');
        } else {
            // Nonaktifkan
            if ($this->publicShareId) {
                $share = Share::find($this->publicShareId);
                if ($share) {
                    $shareService->revokeShare($share);
                }
            }
            $this->publicLinkUrl = '';
            session()->flash('message', 'Tautan berbagi publik telah dinonaktifkan.');
        }
    }

    public function updatePublicPermission(ShareService $shareService): void
    {
        if (! $this->publicShareId) {
            return;
        }

        $share = Share::find($this->publicShareId);
        if ($share) {
            $shareService->updateShare($share, [
                'permission' => $this->publicPermission,
            ]);
            session()->flash('message', 'Izin tautan berhasil diperbarui.');
        }
    }

    public function addCollaborator(int $userId, ShareService $shareService): void
    {
        $item = $this->resolveItem();
        $recipient = User::find($userId);

        if (! $item || ! $recipient) {
            return;
        }

        try {
            $shareService->shareWithUser(
                $item,
                Auth::user(),
                $recipient,
                $this->recipientPermission,
                $this->recipientExpiryDays > 0 ? $this->recipientExpiryDays : null
            );

            $this->userSearch = '';
            session()->flash('message', "Akses berhasil dibagikan kepada {$recipient->name}.");
        } catch (\Exception $e) {
            $this->addError('recipient', $e->getMessage());
        }
    }

    public function removeCollaborator(int $shareId, ShareService $shareService): void
    {
        $share = Share::where('id', $shareId)
            ->where('user_id', Auth::id())
            ->first();

        if ($share) {
            $shareService->deleteShare($share);
            session()->flash('message', 'Akses kolaborator telah dicabut.');
        }
    }

    public function updateCollaboratorPermission(int $shareId, string $permission, ShareService $shareService): void
    {
        $share = Share::where('id', $shareId)
            ->where('user_id', Auth::id())
            ->first();

        if ($share) {
            $shareService->updateShare($share, ['permission' => $permission]);
            session()->flash('message', 'Izin kolaborator berhasil diubah.');
        }
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    protected function resolveItem(): ?Model
    {
        if ($this->type === 'file') {
            return File::find($this->itemId);
        }

        if ($this->type === 'folder') {
            return Folder::find($this->itemId);
        }

        return null;
    }

    public function render()
    {
        $item = $this->resolveItem();
        $collaborators = collect();
        $searchResults = collect();

        if ($item && $this->isOpen) {
            $collaborators = Share::where('user_id', Auth::id())
                ->where('shareable_type', get_class($item))
                ->where('shareable_id', $item->id)
                ->whereNotNull('shared_with_user_id')
                ->with('sharedWithUser')
                ->get();

            if (! empty($this->userSearch)) {
                $existingIds = $collaborators->pluck('shared_with_user_id')->push(Auth::id())->toArray();

                $searchResults = User::whereNotIn('id', $existingIds)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%' . $this->userSearch . '%')
                            ->orWhere('email', 'like', '%' . $this->userSearch . '%');
                    })
                    ->take(5)
                    ->get();
            }
        }

        return view('livewire.share-modal', [
            'collaborators' => $collaborators,
            'searchResults' => $searchResults,
        ]);
    }
}
