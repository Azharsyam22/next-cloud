<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\Share;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Share>
 */
class ShareFactory extends Factory
{
    protected $model = Share::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shareable_type' => File::class,
            'shareable_id' => File::factory(),
            'token' => Str::random(64),
            'permission' => fake()->randomElement(['view', 'download']),
            'expires_at' => now()->addDays(7),
            'shared_with_user_id' => null,
            'is_active' => true,
        ];
    }
}
