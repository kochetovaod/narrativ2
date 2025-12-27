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
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(['super_admin', 'admin', 'content_manager']),
            'is_active' => true,
            'last_login_at' => fake()->dateTimeBetween('-1 month'),
            'telegram_id' => fake()->optional()->unique()->numberBetween(10_000, 9_999_999),
            'telegram_username' => fake()->optional()->userName(),
            'telegram_chat_id' => fake()->optional()->numberBetween(10_000, 9_999_999),
            'telegram_verified_at' => fake()->optional()->dateTimeBetween('-2 months'),
            'remember_token' => Str::random(10),
            'permissions' => [],
        ];
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
