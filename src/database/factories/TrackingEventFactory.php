<?php

namespace Database\Factories;

use App\Models\TrackingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackingEvent>
 */
class TrackingEventFactory extends Factory
{
    protected $model = TrackingEvent::class;

    public function definition(): array
    {
        return [
            'event_type' => $this->faker->randomElement(['form_submit', 'click_tel', 'click_telegram', 'click_whatsapp']),
            'source_url' => $this->faker->url(),
            'utm' => ['utm_campaign' => $this->faker->word()],
            'client_id' => $this->faker->optional()->uuid(),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
