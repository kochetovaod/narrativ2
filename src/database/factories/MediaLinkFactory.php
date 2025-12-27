<?php

namespace Database\Factories;

use App\Models\MediaFile;
use App\Models\MediaLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaLink>
 */
class MediaLinkFactory extends Factory
{
    protected $model = MediaLink::class;

    public function definition(): array
    {
        return [
            'entity_type' => null,
            'entity_id' => null,
            'media_id' => MediaFile::factory(),
            'role' => $this->faker->randomElement(['cover', 'gallery', 'inline', 'attachment']),
            'sort' => $this->faker->numberBetween(0, 10),
            'alt' => $this->faker->sentence(),
        ];
    }
}
