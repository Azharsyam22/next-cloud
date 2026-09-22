<?php

namespace App\Http\Resources;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shareable = $this->shareable;
        $shareableData = null;

        if ($shareable instanceof File) {
            $shareableData = [
                'type' => 'file',
                'id' => $shareable->id,
                'name' => $shareable->original_name,
                'extension' => $shareable->extension,
                'mime_type' => $shareable->mime_type,
                'size' => $shareable->size,
                'formatted_size' => $shareable->formatted_size,
            ];
        } elseif ($shareable instanceof Folder) {
            $shareableData = [
                'type' => 'folder',
                'id' => $shareable->id,
                'name' => $shareable->name,
                'color' => $shareable->color,
            ];
        }

        return [
            'id' => $this->id,
            'token' => $this->token,
            'url' => url("/s/{$this->token}"),
            'permission' => $this->permission,
            'can_download' => $this->canDownload(),
            'is_public' => $this->isPublic(),
            'is_active' => $this->is_active,
            'is_expired' => $this->isExpired(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'item' => $shareableData,
            'owner' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'shared_with' => $this->sharedWithUser ? [
                'id' => $this->sharedWithUser->id,
                'name' => $this->sharedWithUser->name,
                'email' => $this->sharedWithUser->email,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
