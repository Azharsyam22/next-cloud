<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Folder;
use App\Models\User;
use App\Services\ZipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateFolderZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Folder $folder,
        public User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ZipService $zipService): string
    {
        $diskName = config('cloudcampus.disk', 'local');
        $tempZipPath = $zipService->generateZipForFolder($this->folder);

        $uuid = Str::uuid();
        $safeName = $zipService->sanitizePathSegment($this->folder->name);
        $storagePath = "users/{$this->user->id}/archives/{$safeName}_{$uuid}.zip";

        // Simpan arsip yang selesai diproses ke storage user
        $stream = fopen($tempZipPath, 'r');
        Storage::disk($diskName)->put($storagePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Hapus berkas temporary lokal
        if (file_exists($tempZipPath)) {
            @unlink($tempZipPath);
        }

        ActivityLog::record(
            'folder_zip_completed',
            $this->folder,
            "Arsip ZIP untuk folder \"{$this->folder->name}\" selesai diproses di latar belakang.",
            [
                'folder_id' => $this->folder->id,
                'archive_path' => $storagePath,
            ],
            $this->user->id
        );

        return $storagePath;
    }
}
