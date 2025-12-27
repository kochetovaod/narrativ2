<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'key' => $this->faker->unique()->word(),
            'label' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['text', 'textarea', 'phone', 'email', 'select', 'checkbox']),
            'mask' => null,
            'is_required' => $this->faker->boolean(70),
            'sort' => $this->faker->numberBetween(1, 10),
            'options' => ['options' => $this->faker->words(3)],
            'validation_rules' => $this->faker->optional()->regexify('regex:/.+/'),
        ];
    }
}
