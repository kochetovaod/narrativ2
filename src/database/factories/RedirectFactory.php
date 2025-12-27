<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    public function definition(): array
    {
        return [
            'from_path' => '/old-' . $this->faker->unique()->slug(),
            'to_path' => '/new-' . $this->faker->slug(),
            'code' => 301,
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
