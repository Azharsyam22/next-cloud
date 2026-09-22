<?php

namespace App\Livewire;

use App\Services\ShareService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SharedWithMe extends Component
{
    public string $filter = 'all'; // 'all', 'folders', 'files'
    public string $search = '';

    public function render(ShareService $shareService)
    {
        $shares = $shareService->getSharedWithMe(Auth::user());

        if ($this->filter === 'folders') {
            $shares = $shares->filter(fn ($s) => $s->shareable instanceof \App\Models\Folder);
        } elseif ($this->filter === 'files') {
            $shares = $shares->filter(fn ($s) => $s->shareable instanceof \App\Models\File);
        }

        if (! empty($this->search)) {
            $shares = $shares->filter(function ($s) {
                $name = $s->shareable instanceof \App\Models\Folder
                    ? $s->shareable->name
                    : ($s->shareable->original_name ?? '');

                return str_contains(strtolower($name), strtolower($this->search));
            });
        }

        return view('livewire.shared-with-me', [
            'shares' => $shares,
        ]);
    }
}
