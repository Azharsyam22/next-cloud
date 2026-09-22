<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\AdminService;
use App\Services\QuotaService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $accountType = 'all';
    public string $status = 'all';
    public string $sortBy = 'id';
    public string $sortDirection = 'desc';

    public bool $showQuotaModal = false;
    public ?int $selectedUserId = null;
    public string $selectedUserName = '';
    public string $selectedUserCurrentQuota = '';
    public float $customQuotaGb = 5;

    protected $queryString = [
        'search' => ['except' => ''],
        'accountType' => ['except' => 'all'],
        'status' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAccountType()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function toggleSuspend(int $userId, AdminService $adminService)
    {
        $targetUser = User::find($userId);
        if (! $targetUser) {
            return;
        }

        try {
            $isSuspended = $adminService->toggleUserSuspension($targetUser, Auth::user());
            session()->flash('message', $isSuspended
                ? "Akun {$targetUser->name} berhasil ditangguhkan."
                : "Akun {$targetUser->name} berhasil diaktifkan kembali.");
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openQuotaModal(int $userId, QuotaService $quotaService)
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $this->selectedUserId = $user->id;
        $this->selectedUserName = $user->name;
        $this->selectedUserCurrentQuota = $quotaService->formatBytes($user->quota_bytes);
        $this->customQuotaGb = round($user->quota_bytes / (1024 * 1024 * 1024), 2);
        $this->showQuotaModal = true;
    }

    public function closeQuotaModal()
    {
        $this->showQuotaModal = false;
        $this->selectedUserId = null;
    }

    public function setQuotaPreset(int $gb)
    {
        $this->customQuotaGb = $gb;
    }

    public function saveQuota(AdminService $adminService, QuotaService $quotaService)
    {
        if (! $this->selectedUserId) {
            return;
        }

        $targetUser = User::find($this->selectedUserId);
        if (! $targetUser) {
            return;
        }

        $bytes = (int) round($this->customQuotaGb * 1024 * 1024 * 1024);

        try {
            $adminService->overrideUserQuota($targetUser, $bytes, Auth::user());
            session()->flash('message', "Batas kuota {$targetUser->name} berhasil diperbarui menjadi {$quotaService->formatBytes($bytes)}.");
            $this->closeQuotaModal();
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(AdminService $adminService)
    {
        $users = $adminService->getUsers([
            'search' => $this->search,
            'account_type' => $this->accountType,
            'status' => $this->status,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ], 12);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
