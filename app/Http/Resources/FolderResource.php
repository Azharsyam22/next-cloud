<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Folder
 */
class FolderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'subfolders_count' => $this->whenCounted('children', $this->children_count, fn () => $this->children()->count()),
            'files_count' => $this->whenCounted('files', $this->files_count, fn () => $this->files()->count()),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
