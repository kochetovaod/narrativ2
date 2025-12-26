<?php

namespace Database\Factories;

use App\Models\PortfolioCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioCase>
 */
class PortfolioCaseFactory extends Factory
{
    protected $model = PortfolioCase::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'client_name' => $this->faker->company(),
            'is_nda' => $this->faker->boolean(),
            'status' => $this->faker->randomElement(['draft', 'published']),
            'date' => $this->faker->date(),
            'published_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
