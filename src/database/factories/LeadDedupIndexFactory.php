<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadDedupIndex;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadDedupIndex>
 */
class LeadDedupIndexFactory extends Factory
{
    protected $model = LeadDedupIndex::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'contact_key' => $this->faker->unique()->lexify('contact-????'),
            'created_date' => $this->faker->date(),
        ];
    }
}
