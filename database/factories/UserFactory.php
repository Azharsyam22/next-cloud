<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'account_type' => 'public',
            'external_id' => null,
            'password' => static::$password ??= Hash::make('password'),
            'quota_bytes' => (int) config('cloudcampus.default_quota_bytes', 5368709120),
            'used_bytes' => 0,
            'avatar_path' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State untuk akun akademik (SSO).
     */
    public function academic(?string $nimOrNip = null): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'academic',
            'external_id' => $nimOrNip ?? (string) fake()->numerify('##########'),
            'password' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
