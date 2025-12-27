<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $title = $this->faker->words(2, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title . '-' . $this->faker->unique()->numberBetween(1, 9999)),
            'intro_text' => $this->faker->optional()->paragraph(),
            'body' => [
                'blocks' => [
                    ['type' => 'text', 'content' => $this->faker->paragraph()],
                ],
            ],
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->optional()->dateTimeBetween('-2 weeks', 'now'),
            'seo' => [
                'title' => $title,
                'description' => $this->faker->sentence(),
            ],
            'schema_json' => ['type' => 'Category'],
        ];
    }
}
