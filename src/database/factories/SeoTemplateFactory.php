<?php

namespace Database\Factories;

use App\Models\SeoTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoTemplate>
 */
class SeoTemplateFactory extends Factory
{
    protected $model = SeoTemplate::class;

    public function definition(): array
    {
        return [
            'entity_type' => $this->faker->randomElement(['Category', 'Product', 'Service', 'Case', 'News', 'Page']),
            'title_tpl' => '{{title}} — '.$this->faker->company(),
            'description_tpl' => $this->faker->sentence(10),
            'h1_tpl' => '{{h1}}',
            'og_title_tpl' => '{{og_title}}',
            'og_description_tpl' => '{{og_description}}',
            'og_image_mode' => $this->faker->randomElement(['auto', 'manual']),
            'is_default' => $this->faker->boolean(30),
        ];
    }
}
