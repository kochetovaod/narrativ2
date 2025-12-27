<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Services\BreadcrumbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function __construct(
        private BreadcrumbService $breadcrumbService
    ) {}

    /**
     * Главная страница
     */
    public function home(): View
    {
        $breadcrumbs = $this->breadcrumbService->home();

        return view('public.home', compact('breadcrumbs'));
    }

    /**
     * Список категорий продукции
     */
    public function products(): View
    {
        $categories = ProductCategory::where('status', 'published')
            ->with('products')
            ->orderBy('sort')
            ->get();

        $breadcrumbs = $this->breadcrumbService->products();

        return view('public.products.index', compact('categories', 'breadcrumbs'));
    }

    /**
     * Товары в категории
     */
    public function productsByCategory(string $categorySlug): View
    {
        $category = ProductCategory::where('slug', $categorySlug)
            ->where('status', 'published')
            ->firstOrFail();

        $products = Product::where('category_id', $category->id)
            ->where('status', 'published')
            ->orderBy('sort')
            ->get();

        $breadcrumbs = $this->breadcrumbService->productCategory($category);

        return view('public.products.category', compact('category', 'products', 'breadcrumbs'));
    }

    /**
     * Карточка товара
     */
    public function product(string $categorySlug, string $productSlug): View
    {
        $category = ProductCategory::where('slug', $categorySlug)
            ->where('status', 'published')
            ->firstOrFail();

        $product = Product::where('slug', $productSlug)
            ->where('category_id', $category->id)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $category->id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->product($product, $category);

        return view('public.products.show', compact('category', 'product', 'relatedProducts', 'breadcrumbs'));
    }

    /**
     * Список услуг
     */
    public function services(): View
    {
        $services = Service::where('status', 'published')
            ->orderBy('sort')
            ->get();

        $breadcrumbs = $this->breadcrumbService->services();

        return view('public.services.index', compact('services', 'breadcrumbs'));
    }

    /**
     * Карточка услуги
     */
    public function service(string $serviceSlug): View
    {
        $service = Service::where('slug', $serviceSlug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedServices = Service::where('id', '!=', $service->id)
            ->where('status', 'published')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->service($service);

        return view('public.services.show', compact('service', 'relatedServices', 'breadcrumbs'));
    }

    /**
     * Портфолио
     */
    public function portfolio(): View
    {
        $cases = PortfolioCase::where('status', 'published')
            ->orderBy('sort')
            ->get();

        $breadcrumbs = $this->breadcrumbService->portfolio();

        return view('public.portfolio.index', compact('cases', 'breadcrumbs'));
    }

    /**
     * Карточка кейса
     */
    public function portfolioCase(string $caseSlug): View
    {
        $case = PortfolioCase::where('slug', $caseSlug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedCases = PortfolioCase::where('id', '!=', $case->id)
            ->where('status', 'published')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->portfolioCase($case);

        return view('public.portfolio.show', compact('case', 'relatedCases', 'breadcrumbs'));
    }

    /**
     * Новости
     */
    public function news(): View
    {
        $news = NewsPost::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get();

        $breadcrumbs = $this->breadcrumbService->news();

        return view('public.news.index', compact('news', 'breadcrumbs'));
    }

    /**
     * Карточка новости
     */
    public function newsPost(string $newsSlug): View
    {
        $news = NewsPost::where('slug', $newsSlug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedNews = NewsPost::where('id', '!=', $news->id)
            ->where('status', 'published')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->newsPost($news);

        return view('public.news.show', compact('news', 'relatedNews', 'breadcrumbs'));
    }

    /**
     * Статическая страница
     */
    public function page(string $pageSlug): View
    {
        $page = Page::where('slug', $pageSlug)
            ->where('status', 'published')
            ->firstOrFail();

        $breadcrumbs = $this->breadcrumbService->page($page);

        return view('public.pages.show', compact('page', 'breadcrumbs'));
    }

    /**
     * Поиск
     */
    public function search(Request $request): View
    {
        $query = $request->get('q');
        $results = collect();

        if ($query && strlen(trim($query)) > 0) {
            // Поиск по товарам
            $products = Product::where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->get();

            // Поиск по услугам
            $services = Service::where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->get();

            // Поиск по новостям
            $news = NewsPost::where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->get();

            // Поиск по страницам
            $pages = Page::where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                })
                ->get();

            $results = collect([
                'products' => $products,
                'services' => $services,
                'news' => $news,
                'pages' => $pages,
            ]);
        }

        $breadcrumbs = $this->breadcrumbService->search($query);

        return view('public.search', compact('query', 'results', 'breadcrumbs'));
    }

    /**
     * Получение данных для автокомплита поиска
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        $query = $request->get('q');
        $suggestions = collect();

        if ($query && strlen(trim($query)) >= 2) {
            // Ищем в товарах
            $products = Product::where('status', 'published')
                ->where('title', 'like', "%{$query}%")
                ->select('title', 'slug')
                ->limit(3)
                ->get();

            foreach ($products as $product) {
                $suggestions->push([
                    'title' => $product->title,
                    'url' => '/products/'.$product->slug,
                    'type' => 'product',
                ]);
            }

            // Ищем в услугах
            $services = Service::where('status', 'published')
                ->where('title', 'like', "%{$query}%")
                ->select('title', 'slug')
                ->limit(3)
                ->get();

            foreach ($services as $service) {
                $suggestions->push([
                    'title' => $service->title,
                    'url' => '/services/'.$service->slug,
                    'type' => 'service',
                ]);
            }
        }

        return response()->json($suggestions->values());
    }
}
