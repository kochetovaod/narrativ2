<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode(PHP_EOL, $lines), 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('home'))->setLastModificationDate(now()));

        ProductCategory::query()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->each(function (ProductCategory $category) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('products.category', ['categorySlug' => $category->slug]))
                        ->setLastModificationDate($category->updated_at ?? now())
                );
            });

        Product::query()
            ->with('category:id,slug')
            ->published()
            ->orderBy('updated_at', 'desc')
            ->each(function (Product $product) use ($sitemap): void {
                if ($product->category === null) {
                    return;
                }

                $sitemap->add(
                    Url::create(route('products.show', [
                        'categorySlug' => $product->category->slug,
                        'productSlug' => $product->slug,
                    ]))->setLastModificationDate($product->updated_at ?? now())
                );
            });

        Service::query()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->each(function (Service $service) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('services.show', ['serviceSlug' => $service->slug]))
                        ->setLastModificationDate($service->updated_at ?? now())
                );
            });

        PortfolioCase::query()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->each(function (PortfolioCase $case) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('portfolio.show', ['caseSlug' => $case->slug]))
                        ->setLastModificationDate($case->updated_at ?? now())
                );
            });

        NewsPost::query()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->each(function (NewsPost $news) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('news.show', ['newsSlug' => $news->slug]))
                        ->setLastModificationDate($news->updated_at ?? now())
                );
            });

        Page::query()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->each(function (Page $page) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('pages.show', ['pageSlug' => $page->slug]))
                        ->setLastModificationDate($page->updated_at ?? now())
                );
            });

        return $sitemap->toResponse(request());
    }
}
