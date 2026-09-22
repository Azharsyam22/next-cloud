<?php

namespace App\Livewire;

use App\Models\File;
use App\Services\FileService;
use App\Services\QuotaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuotaDashboard extends Component
{
    public function emptyTrash(FileService $fileService)
    {
        $user = Auth::user();
        $trashedFiles = File::onlyTrashed()->where('user_id', $user->id)->get();

        foreach ($trashedFiles as $file) {
            $fileService->permanentDelete($file);
        }

        session()->flash('message', 'Tempat sampah berhasil dikosongkan. Ruang penyimpanan telah dipulihkan.');
    }

    public function recalculate(QuotaService $quotaService)
    {
        $user = Auth::user();
        $quotaService->recalculateUserUsage($user);
        $user->refresh();

        session()->flash('message', 'Sinkronisasi kuota penyimpanan berhasil diperbarui.');
    }

    public function deleteFile(int $fileId, FileService $fileService)
    {
        $user = Auth::user();
        $file = File::where('user_id', $user->id)->find($fileId);

        if ($file) {
            $fileService->delete($file);
            session()->flash('message', 'Berkas "'.$file->original_name.'" berhasil dipindahkan ke tempat sampah.');
        }
    }

    public function render(QuotaService $quotaService)
    {
        $user = Auth::user();
        $stats = $quotaService->getQuotaStats($user);
        $breakdown = $quotaService->getBreakdownByType($user);
        $largestFiles = $quotaService->getLargestFiles($user, 6);

        return view('livewire.quota-dashboard', [
            'stats' => $stats,
            'breakdown' => $breakdown,
            'largestFiles' => $largestFiles,
        ]);
    }
}
