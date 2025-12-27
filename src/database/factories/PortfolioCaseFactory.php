<?php

namespace Database\Factories;

use App\Models\PortfolioCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortfolioCase>
 */
class PortfolioCaseFactory extends Factory
{
    protected $model = PortfolioCase::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title.'-'.$this->faker->unique()->numberBetween(1, 9999)),
            'description' => $this->faker->paragraph(),
            'client_name' => $this->faker->company(),
            'is_nda' => $this->faker->boolean(20),
            'public_client_label' => $this->faker->companySuffix(),
            'status' => $this->faker->randomElement(['draft', 'published']),
            'date' => $this->faker->optional()->date(),
            'published_at' => $this->faker->optional()->dateTimeBetween('-2 months', 'now'),
            'seo' => [
                'title' => $title,
                'description' => $this->faker->sentence(),
            ],
        ];
    }
}
