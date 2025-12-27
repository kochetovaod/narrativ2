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
