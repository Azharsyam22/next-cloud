<?php

namespace App\Livewire;

use App\Models\Share;
use App\Services\ShareService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MySharedLinks extends Component
{
    public string $filter = 'all'; // 'all', 'public', 'private'

    public function revoke(int $shareId, ShareService $shareService): void
    {
        $share = Share::where('id', $shareId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $shareService->revokeShare($share);
        session()->flash('message', 'Tautan berbagi berhasil dinonaktifkan.');
    }

    public function delete(int $shareId, ShareService $shareService): void
    {
        $share = Share::where('id', $shareId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $shareService->deleteShare($share);
        session()->flash('message', 'Tautan berbagi berhasil dihapus permanen.');
    }

    public function activate(int $shareId, ShareService $shareService): void
    {
        $share = Share::where('id', $shareId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $shareService->updateShare($share, ['is_active' => true]);
        session()->flash('message', 'Tautan berbagi berhasil diaktifkan kembali.');
    }

    public function render(ShareService $shareService)
    {
        $query = Share::where('user_id', Auth::id())
            ->with(['shareable', 'sharedWithUser'])
            ->latest();

        if ($this->filter === 'public') {
            $query->whereNull('shared_with_user_id');
        } elseif ($this->filter === 'private') {
            $query->whereNotNull('shared_with_user_id');
        }

        $shares = $query->get();

        return view('livewire.my-shared-links', [
            'shares' => $shares,
        ]);
    }
}
