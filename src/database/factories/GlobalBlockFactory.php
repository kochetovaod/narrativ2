<?php

namespace Database\Factories;

use App\Models\GlobalBlock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GlobalBlock>
 */
class GlobalBlockFactory extends Factory
{
    protected $model = GlobalBlock::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'code' => Str::slug($title . '-' . $this->faker->unique()->numberBetween(1, 9999)),
            'title' => $title,
            'content' => [
                'body' => $this->faker->paragraph(),
            ],
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
