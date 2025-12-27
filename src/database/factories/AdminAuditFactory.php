<?php

namespace Database\Factories;

use App\Models\AdminAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AdminAudit>
 */
class AdminAuditFactory extends Factory
{
    protected $model = AdminAudit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['create', 'update', 'delete', 'publish', 'unpublish']),
            'auditable_type' => $this->faker->randomElement([
                'App\\Models\\Page',
                'App\\Models\\Product',
                'App\\Models\\Service',
                'App\\Models\\PortfolioCase',
                'App\\Models\\NewsPost',
            ]),
            'auditable_id' => $this->faker->numberBetween(1, 100),
            'changes' => [
                'before' => ['title' => $this->faker->sentence()],
                'after' => ['title' => $this->faker->sentence()],
            ],
            'context' => [
                'ip' => $this->faker->ipv4(),
                'request_id' => Str::uuid()->toString(),
            ],
        ];
    }
}
