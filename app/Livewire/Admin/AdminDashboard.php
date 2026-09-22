<?php

namespace App\Livewire\Admin;

use App\Services\AdminService;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function render(AdminService $adminService)
    {
        $stats = $adminService->getSystemStats();

        return view('livewire.admin.admin-dashboard', [
            'stats' => $stats,
        ]);
    }
}
