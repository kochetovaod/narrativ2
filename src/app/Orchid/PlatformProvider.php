<?php

namespace App\Orchid;

use App\Orchid\Permissions\Rbac;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Boot the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    /**
     * Register the main sidebar menu items.
     */
    public function registerMainMenu(): array
    {
        return [
            Menu::make(__('Панель управления'))
                ->icon('monitor')
                ->route('platform.main')
                ->permission(Rbac::PERMISSION_ACCESS)
                ->title(__('Навигация')),

            Menu::make(__('Пользователи'))
                ->icon('user')
                ->route('platform.systems.users')
                ->permission(Rbac::PERMISSION_USERS)
                ->title(__('Система')),

            Menu::make(__('Роли'))
                ->icon('lock')
                ->route('platform.systems.roles')
                ->permission(Rbac::PERMISSION_ROLES),

            Menu::make(__('Категории продукции'))
                ->icon('folder')
                ->route('platform.systems.product_categories')
                ->permission(Rbac::PERMISSION_PRODUCT_CATEGORIES)
                ->title(__('Контент')),

            Menu::make(__('Товары'))
                ->icon('basket')
                ->route('platform.systems.products')
                ->permission(Rbac::PERMISSION_PRODUCTS),

            Menu::make(__('Услуги'))
                ->icon('settings')
                ->route('platform.systems.services')
                ->permission(Rbac::PERMISSION_SERVICES),

            Menu::make(__('Портфолио'))
                ->icon('photo')
                ->route('platform.systems.portfolio_cases')
                ->permission(Rbac::PERMISSION_PORTFOLIO_CASES),

            Menu::make(__('Новости'))
                ->icon('news')
                ->route('platform.systems.news_posts')
                ->permission(Rbac::PERMISSION_NEWS_POSTS),

            Menu::make(__('Страницы'))
                ->icon('document')
                ->route('platform.systems.pages')
                ->permission(Rbac::PERMISSION_PAGES),

            Menu::make(__('Глобальные блоки'))
                ->icon('blocks')
                ->route('platform.systems.global-blocks')
                ->permission(Rbac::PERMISSION_PAGE_BUILDER),

            Menu::make(__('Меню и навигация'))
                ->icon('menu')
                ->route('platform.systems.menu')
                ->permission(Rbac::PERMISSION_MENU),

            Menu::make(__('Формы и заявки'))
                ->icon('envelope')
                ->route('platform.forms.index')
                ->permission(Rbac::PERMISSION_FORMS),

            Menu::make(__('Аналитика и метрики'))
                ->icon('chart')
                ->route('platform.analytics.settings')
                ->permission(Rbac::PERMISSION_ANALYTICS),

            Menu::make(__('Импорт/Экспорт'))
                ->icon('cloud-download')
                ->route('platform.import-export')
                ->permission(Rbac::PERMISSION_IMPORTS),
        ];
    }

    /**
     * Register the user profile menu items.
     */
    public function registerProfileMenu(): array
    {
        return [
            Menu::make(__('Профиль'))
                ->route('platform.profile')
                ->icon('user'),
        ];
    }

    /**
     * Register available permissions for the platform.
     */
    public function registerPermissions(): array
    {
        return collect(Rbac::permissionGroups())
            ->map(static function (array $permissions, string $group): ItemPermission {
                $permissionGroup = ItemPermission::group(__($group));

                foreach ($permissions as $key => $label) {
                    $permissionGroup->addPermission($key, __($label));
                }

                return $permissionGroup;
            })
            ->values()
            ->all();
    }
}
