<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'category_id' => ProductCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title.'-'.$this->faker->unique()->numberBetween(1, 9999)),
            'preview_token' => Str::uuid()->toString(),
            'short_text' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'specs' => [
                'weight' => $this->faker->randomFloat(2, 0.1, 10).' кг',
                'color' => $this->faker->safeColorName(),
            ],
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'seo' => [
                'title' => $title,
                'description' => $this->faker->sentence(),
            ],
            'schema_json' => ['type' => 'product'],
        ];
    }
}
