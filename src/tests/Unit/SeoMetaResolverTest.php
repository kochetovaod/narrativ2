<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\SeoTemplate;
use App\Services\Seo\SeoMetaResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_applied_with_overrides(): void
    {
        SeoTemplate::factory()->create([
            'entity_type' => 'Product',
            'is_default' => true,
            'title_tpl' => '{{title}} | {{brand}}',
            'description_tpl' => 'Desc {{title}}',
            'h1_tpl' => '{{title}}',
            'og_title_tpl' => 'OG {{title}}',
            'og_description_tpl' => 'OGD {{title}}',
            'og_image_mode' => 'auto',
        ]);

        $product = Product::factory()->create([
            'title' => 'Sample',
            'seo' => ['description' => 'Custom desc'],
        ]);

        $meta = (new SeoMetaResolver)->resolve($product, ['brand' => 'Acme']);

        $this->assertSame('Sample | Acme', $meta['title']);
        $this->assertSame('Custom desc', $meta['description']);
        $this->assertSame('Sample', $meta['h1']);
        $this->assertSame('OG Sample', $meta['og_title']);
        $this->assertSame('OGD Sample', $meta['og_description']);
        $this->assertSame('auto', $meta['og_image_mode']);
    }
}
