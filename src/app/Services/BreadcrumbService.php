<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\PortfolioCase;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

class BreadcrumbService
{
    /**
     * Генерирует хлебные крошки для главной страницы
     */
    public function home(): array
    {
        return [
            [
                'title' => 'Главная',
                'url' => route('home', absolute: false),
                'is_active' => true,
            ],
        ];
    }

    /**
     * Генерирует хлебные крошки для категории продукции
     */
    public function productCategory(ProductCategory $category): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Продукция',
            'url' => route('products.index', absolute: false),
            'is_active' => false,
        ];
        $breadcrumbs[] = [
            'title' => $category->title,
            'url' => route('products.category', ['categorySlug' => $category->slug], absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки для товара
     */
    public function product(Product $product, ProductCategory $category): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Продукция',
            'url' => route('products.index', absolute: false),
            'is_active' => false,
        ];
        $breadcrumbs[] = [
            'title' => $category->title,
            'url' => route('products.category', ['categorySlug' => $category->slug], absolute: false),
            'is_active' => false,
        ];
        $breadcrumbs[] = [
            'title' => $product->title,
            'url' => route('products.show', [
                'categorySlug' => $category->slug,
                'productSlug' => $product->slug,
            ], absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки для услуги
     */
    public function service(Service $service): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Услуги',
            'url' => route('services.index', absolute: false),
            'is_active' => false,
        ];
        $breadcrumbs[] = [
            'title' => $service->title,
            'url' => route('services.show', ['serviceSlug' => $service->slug], absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки для портфолио
     */
    public function portfolioCase(PortfolioCase $case): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Портфолио',
            'url' => route('portfolio.index', absolute: false),
            'is_active' => false,
        ];
        $breadcrumbs[] = [
            'title' => $case->title,
            'url' => route('portfolio.show', ['caseSlug' => $case->slug], absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки для новости
     */
    public function newsPost(NewsPost $news): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Новости',
            'url' => route('news.index', absolute: false),
            'is_active' => false,
        ];
        $breadcrumbs[] = [
            'title' => $news->title,
            'url' => route('news.show', ['newsSlug' => $news->slug], absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки для статической страницы
     */
    public function page(Page $page): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => $page->title,
            'url' => route('pages.show', ['pageSlug' => $page->slug], absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки для поиска
     */
    public function search(?string $query = null): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Поиск',
            'url' => route('search', absolute: false),
            'is_active' => true,
        ];

        if ($query) {
            $breadcrumbs[count($breadcrumbs) - 1]['title'] = 'Поиск: '.$query;
        }

        return $breadcrumbs;
    }

    /**
     * Генерирует хлебные крошки на основе текущего маршрута
     */
    public function generateForCurrentRoute(): array
    {
        $routeName = Route::currentRouteName();
        $routeParameters = Route::current()->parameters();

        return match ($routeName) {
            'home' => $this->home(),
            'products.index' => $this->products(),
            'products.category' => $this->productCategoryBySlug($routeParameters['categorySlug'] ?? ''),
            'products.show' => $this->productBySlugs(
                $routeParameters['categorySlug'] ?? '',
                $routeParameters['productSlug'] ?? ''
            ),
            'services.index' => $this->services(),
            'services.show' => $this->serviceBySlug($routeParameters['serviceSlug'] ?? ''),
            'portfolio.index' => $this->portfolio(),
            'portfolio.show' => $this->portfolioCaseBySlug($routeParameters['caseSlug'] ?? ''),
            'news.index' => $this->news(),
            'news.show' => $this->newsPostBySlug($routeParameters['newsSlug'] ?? ''),
            'pages.show' => $this->pageBySlug($routeParameters['pageSlug'] ?? ''),
            'contacts' => $this->contacts(),
            'documents.privacy' => $this->document('Политика конфиденциальности', route('documents.privacy', absolute: false)),
            'documents.consent' => $this->document('Согласие на обработку ПДн', route('documents.consent', absolute: false)),
            'documents.terms' => $this->document('Пользовательское соглашение', route('documents.terms', absolute: false)),
            'documents.cookies' => $this->document('Политика cookie', route('documents.cookies', absolute: false)),
            'search' => $this->search(request()->get('q')),
            default => $this->home(),
        };
    }

    /**
     * Хлебные крошки для списка продуктов
     */
    public function products(): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Продукция',
            'url' => route('products.index', absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Хлебные крошки для списка услуг
     */
    public function services(): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Услуги',
            'url' => route('services.index', absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Хлебные крошки для портфолио
     */
    public function portfolio(): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Портфолио',
            'url' => route('portfolio.index', absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Хлебные крошки для новостей
     */
    public function news(): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Новости',
            'url' => route('news.index', absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Получает категорию по слагу
     */
    private function productCategoryBySlug(string $slug): array
    {
        $category = ProductCategory::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $category) {
            return $this->products();
        }

        return $this->productCategory($category);
    }

    /**
     * Получает товар по слагам
     */
    private function productBySlugs(string $categorySlug, string $productSlug): array
    {
        $category = ProductCategory::where('slug', $categorySlug)
            ->where('status', 'published')
            ->first();

        $product = Product::where('slug', $productSlug)
            ->where('status', 'published')
            ->first();

        if (! $category || ! $product) {
            return $this->products();
        }

        return $this->product($product, $category);
    }

    /**
     * Получает услугу по слагу
     */
    private function serviceBySlug(string $slug): array
    {
        $service = Service::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $service) {
            return $this->services();
        }

        return $this->service($service);
    }

    /**
     * Получает кейс портфолио по слагу
     */
    private function portfolioCaseBySlug(string $slug): array
    {
        $case = PortfolioCase::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $case) {
            return $this->portfolio();
        }

        return $this->portfolioCase($case);
    }

    /**
     * Получает новость по слагу
     */
    private function newsPostBySlug(string $slug): array
    {
        $news = NewsPost::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $news) {
            return $this->news();
        }

        return $this->newsPost($news);
    }

    /**
     * Получает страницу по слагу
     */
    private function pageBySlug(string $slug): array
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page) {
            return $this->home();
        }

        return $this->page($page);
    }

    public function contacts(): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => 'Контакты',
            'url' => route('contacts', absolute: false),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    public function document(string $title, ?string $url = null): array
    {
        $breadcrumbs = $this->home();
        $breadcrumbs[] = [
            'title' => $title,
            'url' => $url ?? request()->getPathInfo(),
            'is_active' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Проверяет, является ли элемент последним (активным)
     */
    public function isLast(array $breadcrumbs, int $index): bool
    {
        return $index === count($breadcrumbs) - 1;
    }

    /**
     * Проверяет, является ли элемент первым
     */
    public function isFirst(array $breadcrumbs, int $index): bool
    {
        return $index === 0;
    }
}
