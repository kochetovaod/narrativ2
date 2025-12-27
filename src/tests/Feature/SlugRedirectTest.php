<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_slug_change_creates_redirect(): void
    {
        $category = ProductCategory::factory()->create(['slug' => 'old-category']);

        $category->update(['slug' => 'new-category']);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/produkciya/old-category',
            'to_path' => '/produkciya/new-category',
            'code' => 301,
            'is_active' => true,
        ]);
    }

    public function test_product_slug_change_with_category_change_creates_redirect(): void
    {
        $firstCategory = ProductCategory::factory()->create(['slug' => 'cat-one']);
        $secondCategory = ProductCategory::factory()->create(['slug' => 'cat-two']);
        $product = Product::factory()->create([
            'category_id' => $firstCategory->id,
            'slug' => 'old-product',
        ]);

        $product->update([
            'slug' => 'new-product',
            'category_id' => $secondCategory->id,
        ]);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/produkciya/cat-one/old-product',
            'to_path' => '/produkciya/cat-two/new-product',
        ]);
    }
}
