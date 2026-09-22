<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Services\AdminService;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogViewer extends Component
{
    use WithPagination;

    public string $search = '';
    public string $action = 'all';

    public ?int $selectedLogId = null;
    public ?array $selectedLogDetails = null;
    public bool $showDetailModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'action' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAction()
    {
        $this->resetPage();
    }

    public function viewDetails(int $logId)
    {
        $log = ActivityLog::with('user')->find($logId);
        if ($log) {
            $this->selectedLogId = $log->id;
            $this->selectedLogDetails = [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user_name' => $log->user ? $log->user->name : 'Sistem',
                'user_email' => $log->user ? $log->user->email : '-',
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'meta' => $log->meta,
                'created_at' => $log->created_at?->format('d M Y, H:i:s'),
            ];
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedLogId = null;
        $this->selectedLogDetails = null;
    }

    public function render(AdminService $adminService)
    {
        $logs = $adminService->getActivityLogs([
            'search' => $this->search,
            'action' => $this->action,
        ], 15);

        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.admin.activity-log-viewer', [
            'logs' => $logs,
            'availableActions' => $actions,
        ]);
    }
}
