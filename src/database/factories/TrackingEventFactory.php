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
            'event_type' => $this->faker->randomElement(['form_submit', 'conversion', 'click', 'page_view', 'form_interaction', 'engagement']),
            'event_name' => $this->faker->randomElement(['form_submit', 'form_start', 'scroll_depth']),
            'data' => ['payload' => $this->faker->words(2, true)],
            'source_url' => $this->faker->optional()->url(),
            'utm' => ['utm_campaign' => $this->faker->word()],
            'client_id' => $this->faker->optional()->uuid(),
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'session_id' => $this->faker->uuid(),
            'page_url' => $this->faker->url(),
            'referer' => $this->faker->optional()->url(),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
