<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormPlacement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormPlacement>
 */
class FormPlacementFactory extends Factory
{
    protected $model = FormPlacement::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'entity_type' => $this->faker->randomElement(['Product', 'Category', 'Service', 'Page', 'GlobalBlock']),
            'entity_id' => $this->faker->numberBetween(1, 50),
            'placement' => $this->faker->randomElement(['inline', 'modal', 'cta_block']),
            'is_enabled' => $this->faker->boolean(90),
            'settings' => [
                'title' => $this->faker->sentence(),
                'button' => $this->faker->word(),
            ],
        ];
    }
}
