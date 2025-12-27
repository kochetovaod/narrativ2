<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'form_code' => $this->faker->randomElement(['callback', 'calc', 'question']),
            'status' => $this->faker->randomElement(['new', 'in_progress', 'closed']),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'payload' => [
                'message' => $this->faker->sentence(),
                'name' => $this->faker->name(),
            ],
            'source_url' => $this->faker->url(),
            'page_title' => $this->faker->sentence(),
            'utm' => ['utm_source' => 'google'],
            'consent_given' => true,
            'consent_doc_url' => $this->faker->url(),
            'consent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'manager_comment' => $this->faker->optional()->sentence(),
        ];
    }
}
