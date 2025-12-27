<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        $code = $this->faker->unique()->randomElement(['callback', 'calc', 'question']);

        return [
            'code' => $code,
            'title' => ucfirst($code) . ' form',
            'is_active' => true,
            'notification_email' => [$this->faker->safeEmail()],
            'notification_telegram' => ['chat_id' => $this->faker->numberBetween(10_000, 9_999_999)],
            'captcha_mode' => $this->faker->randomElement(['off', 'on', 'adaptive']),
        ];
    }
}
