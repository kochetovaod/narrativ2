<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => $this->faker->sentence(2),
            'url' => '/' . $this->faker->slug(),
            'entity_type' => null,
            'entity_id' => null,
            'sort' => $this->faker->numberBetween(0, 20),
            'is_visible' => $this->faker->boolean(90),
        ];
    }
}
