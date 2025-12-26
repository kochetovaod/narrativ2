<?php

namespace App\Orchid;

use App\Orchid\Screens\DashboardScreen;
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
                ->title(__('Навигация')),

            Menu::make(__('Пользователи'))
                ->icon('user')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Система')),

            Menu::make(__('Роли'))
                ->icon('lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
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
        return [
            ItemPermission::group(__('Система'))
                ->addPermission('platform.systems.users', __('Управление пользователями'))
                ->addPermission('platform.systems.roles', __('Управление ролями')),
        ];
    }
}
