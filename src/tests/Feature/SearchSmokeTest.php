<?php

namespace Tests\Feature;

use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\Scout;
use Tests\TestCase;

class SearchSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_meilisearch_driver_is_enabled_by_default(): void
    {
        $this->assertSame('meilisearch', config('scout.driver'));
        $this->assertSame(
            env('MEILISEARCH_HOST', 'http://meilisearch:7700'),
            config('scout.meilisearch.host')
        );
        $this->assertTrue(config('scout.queue'));
        $this->assertTrue(config('scout.after_commit'));
    }

    public function test_index_settings_match_searchable_models(): void
    {
        $settings = config('scout.meilisearch.index-settings');

        $this->assertSame(['category_id', 'status'], $settings['products']['filterableAttributes']);
        $this->assertSame(['published_at', 'created_at'], $settings['products']['sortableAttributes']);
        $this->assertSame(['title', 'description', 'short_text'], $settings['products']['searchableAttributes']);

        $this->assertSame(['is_nda', 'status'], $settings['portfolio_cases']['filterableAttributes']);
        $this->assertSame(['date', 'published_at'], $settings['portfolio_cases']['sortableAttributes']);
        $this->assertSame(['title', 'description', 'client_name'], $settings['portfolio_cases']['searchableAttributes']);

        $this->assertSame(['status'], $settings['services']['filterableAttributes']);
        $this->assertSame(['title', 'content'], $settings['services']['searchableAttributes']);
        $this->assertSame(['published_at'], $settings['services']['sortableAttributes']);

        $product = Product::factory()->make(['id' => 1]);
        $portfolioCase = PortfolioCase::factory()->make(['id' => 2]);
        $service = Service::factory()->make(['id' => 3]);

        $this->assertSame('products', $product->searchableAs());
        $this->assertSame('portfolio_cases', $portfolioCase->searchableAs());
        $this->assertSame('services', $service->searchableAs());
    }

    public function test_models_can_be_sent_to_scout(): void
    {
        Scout::fake();

        $product = Product::factory()->create(['id' => 10]);
        $portfolioCase = PortfolioCase::factory()->create(['id' => 11]);
        $service = Service::factory()->create(['id' => 12]);

        $product->searchable();
        $portfolioCase->searchable();
        $service->searchable();

        Scout::assertSearchable([$product, $portfolioCase, $service]);

        $this->assertArrayHasKey('title', $product->toSearchableArray());
        $this->assertArrayHasKey('client_name', $portfolioCase->toSearchableArray());
        $this->assertArrayHasKey('content', $service->toSearchableArray());
    }
}
