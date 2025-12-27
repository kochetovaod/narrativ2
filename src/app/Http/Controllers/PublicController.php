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
use App\Services\FormPlacementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
        private FormPlacementService $formPlacementService
    ) {}

    /**
     * Главная страница
     */
    public function home(): View
    {
        $categories = ProductCategory::query()
            ->published()
            ->orderBy('title')
            ->limit(6)
            ->get();

        $services = Service::query()
            ->published()
            ->orderBy('title')
            ->limit(6)
            ->get();

        $portfolioCases = PortfolioCase::query()
            ->published()
            ->orderByDesc('date')
            ->limit(3)
            ->get();

        $news = NewsPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $breadcrumbs = $this->breadcrumbService->home();

        return view('public.home', compact(
            'breadcrumbs',
            'categories',
            'services',
            'portfolioCases',
            'news'
        ));
    }

    /**
     * Список категорий продукции
     */
    public function products(): View
    {
        $categories = ProductCategory::query()
            ->published()
            ->with(['products' => fn ($query) => $query->published()->orderBy('title')])
            ->orderBy('title')
            ->get();

        $breadcrumbs = $this->breadcrumbService->products();

        return view('public.products.index', compact('categories', 'breadcrumbs'));
    }

    /**
     * Товары в категории
     */
    public function productsByCategory(string $categorySlug): View
    {
        $category = ProductCategory::query()
            ->published()
            ->where('slug', $categorySlug)
            ->firstOrFail();

        $products = Product::query()
            ->published()
            ->where('category_id', $category->id)
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $breadcrumbs = $this->breadcrumbService->productCategory($category);

        return view('public.products.category', compact('category', 'products', 'breadcrumbs'));
    }

    /**
     * Карточка товара
     */
    public function product(string $categorySlug, string $productSlug): View
    {
        $category = ProductCategory::query()
            ->published()
            ->where('slug', $categorySlug)
            ->firstOrFail();

        $product = Product::query()
            ->with('category')
            ->published()
            ->where('slug', $productSlug)
            ->where('category_id', $category->id)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->published()
            ->where('category_id', $category->id)
            ->where('id', '!=', $product->id)
            ->orderBy('title')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->product($product, $category);

        return view('public.products.show', [
            'breadcrumbs' => $breadcrumbs,
            'category' => $category,
            'formPlacements' => $this->formPlacementService->forEntity($product),
            'pageTitle' => $product->seo['title'] ?? $product->title,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Список услуг
     */
    public function services(): View
    {
        $services = Service::query()
            ->published()
            ->orderBy('title')
            ->get();

        $breadcrumbs = $this->breadcrumbService->services();

        return view('public.services.index', compact('services', 'breadcrumbs'));
    }

    /**
     * Карточка услуги
     */
    public function service(string $serviceSlug): View
    {
        $service = Service::query()
            ->with('portfolioCases')
            ->published()
            ->where('slug', $serviceSlug)
            ->firstOrFail();

        $relatedServices = Service::query()
            ->published()
            ->where('id', '!=', $service->id)
            ->orderBy('title')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->service($service);

        return view('public.services.show', [
            'breadcrumbs' => $breadcrumbs,
            'formPlacements' => $this->formPlacementService->forEntity($service),
            'pageTitle' => $service->seo['title'] ?? $service->title,
            'relatedServices' => $relatedServices,
            'service' => $service,
        ]);
    }

    /**
     * Портфолио
     */
    public function portfolio(Request $request): View
    {
        $productSlug = $request->get('product');
        $serviceSlug = $request->get('service');

        $query = PortfolioCase::query()
            ->with(['products:id,title,slug', 'services:id,title,slug'])
            ->published();

        if (! empty($productSlug)) {
            $query->whereHas('products', fn ($q) => $q->where('slug', $productSlug));
        }

        if (! empty($serviceSlug)) {
            $query->whereHas('services', fn ($q) => $q->where('slug', $serviceSlug));
        }

        $cases = $query
            ->orderByDesc('date')
            ->orderBy('title')
            ->paginate(9)
            ->withQueryString();

        $products = Product::query()
            ->published()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $services = Service::query()
            ->published()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $breadcrumbs = $this->breadcrumbService->portfolio();

        return view('public.portfolio.index', [
            'cases' => $cases,
            'breadcrumbs' => $breadcrumbs,
            'products' => $products,
            'services' => $services,
            'selectedProduct' => $productSlug,
            'selectedService' => $serviceSlug,
        ]);
    }

    /**
     * Карточка кейса
     */
    public function portfolioCase(string $caseSlug): View
    {
        $case = PortfolioCase::query()
            ->with([
                'products' => fn ($query) => $query
                    ->select('products.id', 'products.title', 'products.slug', 'products.category_id')
                    ->with('category:id,slug'),
                'services:id,title,slug',
            ])
            ->published()
            ->where('slug', $caseSlug)
            ->firstOrFail();

        $relatedCases = PortfolioCase::query()
            ->published()
            ->where('id', '!=', $case->id)
            ->orderByDesc('date')
            ->limit(4)
            ->get();

        $breadcrumbs = $this->breadcrumbService->portfolioCase($case);

        return view('public.portfolio.show', [
            'breadcrumbs' => $breadcrumbs,
            'case' => $case,
            'formPlacements' => $this->formPlacementService->forEntity($case),
            'pageTitle' => $case->seo['title'] ?? $case->title,
            'relatedCases' => $relatedCases,
        ]);
    }

    /**
     * Новости
     */
    public function news(): View
    {
        $news = NewsPost::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $breadcrumbs = $this->breadcrumbService->news();

        return view('public.news.index', compact('news', 'breadcrumbs'));
    }

    /**
     * Карточка новости
     */
    public function newsPost(string $newsSlug): View
    {
        $news = NewsPost::query()
            ->published()
            ->where('slug', $newsSlug)
            ->firstOrFail();

        $relatedNews = NewsPost::query()
            ->published()
            ->where('id', '!=', $news->id)
            ->orderByDesc('published_at')
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
        $page = $this->findPageBySlugOrFail($pageSlug);

        $breadcrumbs = $this->breadcrumbService->page($page);

        return view('public.pages.show', [
            'breadcrumbs' => $breadcrumbs,
            'formPlacements' => $this->formPlacementService->forEntity($page),
            'page' => $page,
            'pageTitle' => $page->seo['title'] ?? $page->title,
        ]);
    }

    /**
     * Страница контактов
     */
    public function contacts(): View
    {
        $page = $this->findPageByCodeOrSlugOrFail('contacts', 'kontakty');
        $breadcrumbs = $this->breadcrumbService->contacts();

        return view('public.pages.contacts', [
            'breadcrumbs' => $breadcrumbs,
            'formPlacements' => $this->formPlacementService->forEntity($page),
            'page' => $page,
            'pageTitle' => $page->seo['title'] ?? $page->title,
        ]);
    }

    /**
     * Юридические документы
     */
    public function document(string $documentCode): View
    {
        $page = $this->findPageByCodeOrSlugOrFail($documentCode, $documentCode);
        $breadcrumbs = $this->breadcrumbService->document(
            $page->title,
            route(request()->route()->getName(), absolute: false)
        );

        return view('public.pages.document', [
            'breadcrumbs' => $breadcrumbs,
            'formPlacements' => $this->formPlacementService->forEntity($page),
            'page' => $page,
            'pageTitle' => $page->seo['title'] ?? $page->title,
        ]);
    }

    /**
     * Поиск
     */
    public function search(Request $request): View
    {
        $query = trim((string) $request->get('q'));
        $results = collect();

        if ($query !== '') {
            // Поиск по товарам
            $products = Product::query()
                ->with('category:id,slug')
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderBy('title')
                ->limit(15)
                ->get();

            // Поиск по услугам
            $services = Service::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->orderBy('title')
                ->limit(15)
                ->get();

            // Поиск по новостям
            $news = NewsPost::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->orderByDesc('published_at')
                ->limit(15)
                ->get();

            $portfolio = PortfolioCase::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderByDesc('date')
                ->limit(15)
                ->get();

            // Поиск по страницам
            $pages = Page::query()
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%");
                })
                ->orderBy('title')
                ->limit(15)
                ->get();

            $results = collect([
                'products' => $products,
                'services' => $services,
                'portfolio' => $portfolio,
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
        $query = trim((string) $request->get('q'));
        $suggestions = collect();

        if (strlen($query) >= 2) {
            // Ищем в товарах
            $products = Product::query()
                ->with('category:id,slug')
                ->published()
                ->where('title', 'like', "%{$query}%")
                ->select('id', 'title', 'slug', 'category_id')
                ->limit(3)
                ->get();

            foreach ($products as $product) {
                if ($product->category === null) {
                    continue;
                }

                $suggestions->push([
                    'title' => $product->title,
                    'url' => route('products.show', [
                        'categorySlug' => $product->category->slug,
                        'productSlug' => $product->slug,
                    ]),
                    'type' => 'product',
                ]);
            }

            // Ищем в услугах
            $services = Service::query()
                ->published()
                ->where('title', 'like', "%{$query}%")
                ->select('title', 'slug')
                ->limit(3)
                ->get();

            foreach ($services as $service) {
                $suggestions->push([
                    'title' => $service->title,
                    'url' => route('services.show', ['serviceSlug' => $service->slug]),
                    'type' => 'service',
                ]);
            }

            $news = NewsPost::query()
                ->published()
                ->where('title', 'like', "%{$query}%")
                ->select('title', 'slug')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();

            foreach ($news as $post) {
                $suggestions->push([
                    'title' => $post->title,
                    'url' => route('news.show', ['newsSlug' => $post->slug]),
                    'type' => 'news',
                ]);
            }
        }

        return response()->json($suggestions->values());
    }

    private function findPageBySlugOrFail(string $slug): Page
    {
        return Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function findPageByCodeOrSlugOrFail(string $code, string $slug): Page
    {
        return Page::query()
            ->published()
            ->where(fn ($query) => $query
                ->where('code', $code)
                ->orWhere('slug', $slug))
            ->firstOrFail();
    }
}
