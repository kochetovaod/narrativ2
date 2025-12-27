<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = $this->faker->words(2, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title.'-'.$this->faker->unique()->numberBetween(1, 9999)),
            'preview_token' => Str::uuid()->toString(),
            'content' => [
                ['type' => 'text', 'value' => $this->faker->paragraph()],
            ],
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->optional()->dateTimeBetween('-3 months', 'now'),
            'seo' => [
                'title' => $title,
                'description' => $this->faker->sentence(),
            ],
            'schema_json' => ['type' => 'Service'],
            'show_cases' => $this->faker->boolean(80),
        ];
    }
}
