<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'code' => null,
            'title' => $title,
            'slug' => Str::slug($title.'-'.$this->faker->unique()->numberBetween(1, 9999)),
            'preview_token' => Str::uuid()->toString(),
            'sections' => [
                ['type' => 'text', 'value' => $this->faker->paragraph()],
            ],
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->optional()->dateTimeBetween('-2 months', 'now'),
            'seo' => [
                'title' => $title,
                'description' => $this->faker->sentence(),
            ],
        ];
    }
}
