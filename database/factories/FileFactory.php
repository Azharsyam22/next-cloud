<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $ext = fake()->randomElement(['pdf', 'jpg', 'png', 'docx', 'zip']);
        $uuid = Str::uuid();

        return [
            'user_id' => User::factory(),
            'folder_id' => null,
            'original_name' => fake()->word().'.'.$ext,
            'stored_name' => $uuid.'.'.$ext,
            'storage_path' => 'users/1/files/'.$uuid.'.'.$ext,
            'mime_type' => match ($ext) {
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'zip' => 'application/zip',
            },
            'size' => fake()->numberBetween(1024, 10485760), // 1 KB - 10 MB
            'thumbnail_path' => null,
            'is_favorite' => false,
        ];
    }
}
