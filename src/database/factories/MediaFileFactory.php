<?php

namespace Database\Factories;

use App\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaFile>
 */
class MediaFileFactory extends Factory
{
    protected $model = MediaFile::class;

    public function definition(): array
    {
        return [
            'disk' => 'local',
            'path' => 'uploads/'.$this->faker->uuid().'.jpg',
            'original_name' => $this->faker->word().'.jpg',
            'mime' => 'image/jpeg',
            'size' => $this->faker->numberBetween(10_000, 2_000_000),
            'width' => $this->faker->numberBetween(640, 1920),
            'height' => $this->faker->numberBetween(480, 1080),
        ];
    }
}
