<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Services\Seo\SchemaOrgGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaOrgGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_schema_with_override(): void
    {
        $category = ProductCategory::factory()->create(['title' => 'Категория']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'title' => 'Товар',
            'schema_json' => ['color' => 'red'],
        ]);

        $schema = (new SchemaOrgGenerator)->forEntity($product);

        $this->assertSame('Product', $schema['@type']);
        $this->assertSame('Товар', $schema['name']);
        $this->assertSame('Категория', $schema['category']);
        $this->assertSame('red', $schema['color']);
    }

    public function test_service_schema_defaults(): void
    {
        $service = Service::factory()->create([
            'title' => 'Услуга',
            'schema_json' => null,
            'content' => ['intro' => 'Описание услуги'],
        ]);

        $schema = (new SchemaOrgGenerator)->forEntity($service);

        $this->assertSame('Service', $schema['@type']);
        $this->assertSame('Услуга', $schema['name']);
        $this->assertSame('Описание услуги', $schema['description']);
    }
}
