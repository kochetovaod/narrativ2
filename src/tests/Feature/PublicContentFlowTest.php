<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_public_routes_are_accessible(): void
    {
        $category = ProductCategory::factory()->create([
            'title' => 'Видимая категория',
            'slug' => 'visible-category',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $product = Product::factory()->for($category, 'category')->create([
            'title' => 'Видимый товар',
            'slug' => 'visible-product',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $draftProduct = Product::factory()->for($category, 'category')->create([
            'title' => 'Видимый черновик товара',
            'slug' => 'draft-product',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $service = Service::factory()->create([
            'title' => 'Видимая услуга',
            'slug' => 'visible-service',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $draftService = Service::factory()->create([
            'title' => 'Черновая услуга',
            'slug' => 'draft-service',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $portfolioCase = PortfolioCase::factory()->create([
            'title' => 'Видимый кейс',
            'slug' => 'visible-case',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $portfolioCase->products()->attach($product);
        $portfolioCase->services()->attach($service);

        $draftCase = PortfolioCase::factory()->create([
            'title' => 'Черновой кейс',
            'slug' => 'draft-case',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $news = NewsPost::factory()->create([
            'title' => 'Видимая новость',
            'slug' => 'visible-news',
            'status' => 'published',
            'published_at' => now(),
            'content' => 'Контент для поиска',
        ]);

        $draftNews = NewsPost::factory()->create([
            'title' => 'Черновая новость',
            'slug' => 'draft-news',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $page = Page::factory()->create([
            'title' => 'Видимая страница',
            'slug' => 'about-company',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $draftPage = Page::factory()->create([
            'title' => 'Черновая страница',
            'slug' => 'hidden-page',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($category->title)
            ->assertSee($service->title)
            ->assertSee($portfolioCase->title)
            ->assertSee($news->title);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee($category->title)
            ->assertDontSee($draftProduct->title);

        $this->get(route('products.show', [$category->slug, $product->slug]))
            ->assertOk()
            ->assertSee($product->title);

        $this->get(route('products.show', [$category->slug, $draftProduct->slug]))
            ->assertNotFound();

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee($service->title)
            ->assertDontSee($draftService->title);

        $this->get(route('services.show', $service->slug))
            ->assertOk()
            ->assertSee($service->title);

        $this->get(route('services.show', $draftService->slug))
            ->assertNotFound();

        $this->get(route('portfolio.index', ['product' => $product->slug]))
            ->assertOk()
            ->assertSee($portfolioCase->title);

        $this->get(route('portfolio.show', $portfolioCase->slug))
            ->assertOk()
            ->assertSee($portfolioCase->title);

        $this->get(route('portfolio.show', $draftCase->slug))
            ->assertNotFound();

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee($news->title)
            ->assertDontSee($draftNews->title);

        $this->get(route('news.show', $news->slug))
            ->assertOk()
            ->assertSee($news->title);

        $this->get(route('news.show', $draftNews->slug))
            ->assertNotFound();

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee($page->title);

        $this->get(route('pages.show', $draftPage->slug))
            ->assertNotFound();

        $searchResponse = $this->get(route('search', ['q' => 'Видимая']));

        $searchResponse->assertOk()
            ->assertSee($product->title)
            ->assertSee($service->title)
            ->assertSee($portfolioCase->title)
            ->assertSee($news->title)
            ->assertSee($page->title)
            ->assertDontSee($draftProduct->title)
            ->assertDontSee($draftService->title);
    }
}
