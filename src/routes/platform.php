<?php

declare(strict_types=1);

use App\Orchid\Screens\Analytics\AnalyticsSettingsScreen;
use App\Orchid\Screens\DashboardScreen;
use App\Orchid\Screens\Form\FormEditScreen;
use App\Orchid\Screens\Form\FormListScreen;
use App\Orchid\Screens\GlobalBlock\GlobalBlockEditScreen;
use App\Orchid\Screens\GlobalBlock\GlobalBlockListScreen;
use App\Orchid\Screens\ImportExport\ImportExportScreen;
use App\Orchid\Screens\Lead\LeadListScreen;
use App\Orchid\Screens\Menu\MenuEditScreen;
use App\Orchid\Screens\Menu\MenuListScreen;
use App\Orchid\Screens\NewsPost\NewsPostEditScreen;
use App\Orchid\Screens\NewsPost\NewsPostListScreen;
use App\Orchid\Screens\Page\PageEditScreen;
use App\Orchid\Screens\Page\PageListScreen;
use App\Orchid\Screens\PortfolioCase\PortfolioCaseEditScreen;
use App\Orchid\Screens\PortfolioCase\PortfolioCaseListScreen;
use App\Orchid\Screens\Product\ProductEditScreen;
use App\Orchid\Screens\Product\ProductListScreen;
use App\Orchid\Screens\ProductCategory\ProductCategoryEditScreen;
use App\Orchid\Screens\ProductCategory\ProductCategoryListScreen;
use App\Orchid\Screens\Roles\RoleEditScreen;
use App\Orchid\Screens\Roles\RoleListScreen;
use App\Orchid\Screens\Service\ServiceEditScreen;
use App\Orchid\Screens\Service\ServiceListScreen;
use App\Orchid\Screens\Users\UserEditScreen;
use App\Orchid\Screens\Users\UserListScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::middleware((array) config('platform.middleware.private'))
    ->domain((string) config('platform.domain'))
    ->prefix((string) config('platform.prefix', 'admin'))
    ->group(function (): void {
        Route::screen('main', DashboardScreen::class)
            ->name('platform.main')
            ->breadcrumbs(fn (Trail $trail) => $trail->push(__('Панель управления'), route('platform.main')));

        Route::screen('users/{user}/edit', UserEditScreen::class)
            ->name('platform.systems.users.edit')
            ->breadcrumbs(fn (Trail $trail, $user) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Пользователи'), route('platform.systems.users'))
                ->push(__('Редактирование пользователя'), route('platform.systems.users.edit', $user)));

        Route::screen('users/create', UserEditScreen::class)
            ->name('platform.systems.users.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Пользователи'), route('platform.systems.users'))
                ->push(__('Создание пользователя'), route('platform.systems.users.create')));

        Route::screen('users', UserListScreen::class)
            ->name('platform.systems.users')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Пользователи'), route('platform.systems.users')));

        Route::screen('roles/{role}/edit', RoleEditScreen::class)
            ->name('platform.systems.roles.edit')
            ->breadcrumbs(fn (Trail $trail, $role) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Роли'), route('platform.systems.roles'))
                ->push(__('Редактирование роли'), route('platform.systems.roles.edit', $role)));

        Route::screen('roles/create', RoleEditScreen::class)
            ->name('platform.systems.roles.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Роли'), route('platform.systems.roles'))
                ->push(__('Создание роли'), route('platform.systems.roles.create')));

        Route::screen('roles', RoleListScreen::class)
            ->name('platform.systems.roles')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Роли'), route('platform.systems.roles')));

        // Контентные модули
        Route::screen('product-categories/{category}/edit', ProductCategoryEditScreen::class)
            ->name('platform.systems.product_categories.edit')
            ->breadcrumbs(fn (Trail $trail, $category) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Категории продукции'), route('platform.systems.product_categories'))
                ->push(__('Редактирование категории'), route('platform.systems.product_categories.edit', $category)));

        Route::screen('product-categories/create', ProductCategoryEditScreen::class)
            ->name('platform.systems.product_categories.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Категории продукции'), route('platform.systems.product_categories'))
                ->push(__('Создание категории'), route('platform.systems.product_categories.create')));

        Route::screen('product-categories', ProductCategoryListScreen::class)
            ->name('platform.systems.product_categories')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Категории продукции'), route('platform.systems.product_categories')));

        Route::screen('products/{product}/edit', ProductEditScreen::class)
            ->name('platform.systems.products.edit')
            ->breadcrumbs(fn (Trail $trail, $product) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Товары'), route('platform.systems.products'))
                ->push(__('Редактирование товара'), route('platform.systems.products.edit', $product)));

        Route::screen('products/create', ProductEditScreen::class)
            ->name('platform.systems.products.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Товары'), route('platform.systems.products'))
                ->push(__('Создание товара'), route('platform.systems.products.create')));

        Route::screen('products', ProductListScreen::class)
            ->name('platform.systems.products')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Товары'), route('platform.systems.products')));

        Route::screen('services/{service}/edit', ServiceEditScreen::class)
            ->name('platform.systems.services.edit')
            ->breadcrumbs(fn (Trail $trail, $service) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Услуги'), route('platform.systems.services'))
                ->push(__('Редактирование услуги'), route('platform.systems.services.edit', $service)));

        Route::screen('services/create', ServiceEditScreen::class)
            ->name('platform.systems.services.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Услуги'), route('platform.systems.services'))
                ->push(__('Создание услуги'), route('platform.systems.services.create')));

        Route::screen('services', ServiceListScreen::class)
            ->name('platform.systems.services')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Услуги'), route('platform.systems.services')));

        Route::screen('portfolio-cases/{case}/edit', PortfolioCaseEditScreen::class)
            ->name('platform.systems.portfolio_cases.edit')
            ->breadcrumbs(fn (Trail $trail, $case) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Портфолио'), route('platform.systems.portfolio_cases'))
                ->push(__('Редактирование кейса'), route('platform.systems.portfolio_cases.edit', $case)));

        Route::screen('portfolio-cases/create', PortfolioCaseEditScreen::class)
            ->name('platform.systems.portfolio_cases.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Портфолио'), route('platform.systems.portfolio_cases'))
                ->push(__('Создание кейса'), route('platform.systems.portfolio_cases.create')));

        Route::screen('portfolio-cases', PortfolioCaseListScreen::class)
            ->name('platform.systems.portfolio_cases')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Портфолио'), route('platform.systems.portfolio_cases')));

        Route::screen('news-posts/{news}/edit', NewsPostEditScreen::class)
            ->name('platform.systems.news_posts.edit')
            ->breadcrumbs(fn (Trail $trail, $news) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Новости'), route('platform.systems.news_posts'))
                ->push(__('Редактирование новости'), route('platform.systems.news_posts.edit', $news)));

        Route::screen('news-posts/create', NewsPostEditScreen::class)
            ->name('platform.systems.news_posts.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Новости'), route('platform.systems.news_posts'))
                ->push(__('Создание новости'), route('platform.systems.news_posts.create')));

        Route::screen('news-posts', NewsPostListScreen::class)
            ->name('platform.systems.news_posts')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Новости'), route('platform.systems.news_posts')));

        Route::screen('pages/{page}/edit', PageEditScreen::class)
            ->name('platform.systems.pages.edit')
            ->breadcrumbs(fn (Trail $trail, $page) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Страницы'), route('platform.systems.pages'))
                ->push(__('Редактирование страницы'), route('platform.systems.pages.edit', $page)));

        Route::screen('pages/create', PageEditScreen::class)
            ->name('platform.systems.pages.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Страницы'), route('platform.systems.pages'))
                ->push(__('Создание страницы'), route('platform.systems.pages.create')));

        Route::screen('pages', PageListScreen::class)
            ->name('platform.systems.pages')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Страницы'), route('platform.systems.pages')));

        // Глобальные блоки
        Route::screen('global-blocks/{block}/edit', GlobalBlockEditScreen::class)
            ->name('platform.systems.global-blocks.edit')
            ->breadcrumbs(fn (Trail $trail, $block) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Глобальные блоки'), route('platform.systems.global-blocks'))
                ->push(__('Редактирование блока'), route('platform.systems.global-blocks.edit', $block)));

        Route::screen('global-blocks/create', GlobalBlockEditScreen::class)
            ->name('platform.systems.global-blocks.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Глобальные блоки'), route('platform.systems.global-blocks'))
                ->push(__('Создание блока'), route('platform.systems.global-blocks.create')));

        Route::screen('global-blocks', GlobalBlockListScreen::class)
            ->name('platform.systems.global-blocks')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Глобальные блоки'), route('platform.systems.global-blocks')));

        // Меню и навигация
        Route::screen('menu/edit/{menuCode}', MenuEditScreen::class)
            ->name('platform.systems.menu.edit')
            ->breadcrumbs(fn (Trail $trail, $menuCode) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Меню и навигация'), route('platform.systems.menu'))
                ->push(__('Редактирование меню'), route('platform.systems.menu.edit', $menuCode)));

        Route::screen('menu', MenuListScreen::class)
            ->name('platform.systems.menu')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Меню и навигация'), route('platform.systems.menu')));

        // Формы и заявки
        Route::screen('forms/{form}/edit', FormEditScreen::class)
            ->name('platform.forms.edit')
            ->breadcrumbs(fn (Trail $trail, $form) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Формы и заявки'), route('platform.forms.index'))
                ->push(__('Редактирование формы'), route('platform.forms.edit', $form)));

        Route::screen('forms/create', FormEditScreen::class)
            ->name('platform.forms.create')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Формы и заявки'), route('platform.forms.index'))
                ->push(__('Создание формы'), route('platform.forms.create')));

        Route::screen('forms', FormListScreen::class)
            ->name('platform.forms.index')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Формы и заявки'), route('platform.forms.index')));

        Route::screen('leads', LeadListScreen::class)
            ->name('platform.leads.index')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Формы и заявки'), route('platform.forms.index'))
                ->push(__('Заявки'), route('platform.leads.index')));

        // Аналитика и метрики
        Route::screen('analytics/settings', AnalyticsSettingsScreen::class)
            ->name('platform.analytics.settings')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Аналитика и метрики'), route('platform.analytics.settings')));

        // Импорт и экспорт
        Route::middleware('platform.imports')->group(function (): void {
            Route::screen('import-export', ImportExportScreen::class)
                ->name('platform.import-export')
                ->breadcrumbs(fn (Trail $trail) => $trail
                    ->push(__('Панель управления'), route('platform.main'))
                    ->push(__('Импорт/Экспорт'), route('platform.import-export')));

            Route::post('import-export/import', [ImportExportScreen::class, 'import'])
                ->name('platform.import-export.import');

            Route::post('import-export/export', [ImportExportScreen::class, 'export'])
                ->name('platform.import-export.export');

            Route::post('import-export/preview', [ImportExportScreen::class, 'preview'])
                ->name('platform.import-export.preview');

            Route::get('import-export/download/{logId}', [ImportExportScreen::class, 'download'])
                ->name('platform.import-export.download');
        });
    });
