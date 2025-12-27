<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->randomElement(['header', 'footer']),
            'title' => $this->faker->sentence(3),
        ];
    }
}
