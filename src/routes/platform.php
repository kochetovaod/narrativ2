<?php

declare(strict_types=1);

use App\Orchid\Screens\DashboardScreen;
use Illuminate\Support\Facades\Route;
use Orchid\Platform\Screens\Role\RoleEditScreen;
use Orchid\Platform\Screens\Role\RoleListScreen;
use Orchid\Platform\Screens\User\UserEditScreen;
use Orchid\Platform\Screens\User\UserListScreen;
use Orchid\Platform\Screens\User\UserProfileScreen;
use Tabuna\Breadcrumbs\Trail;

Route::middleware((array) config('platform.middleware.private'))
    ->domain((string) config('platform.domain'))
    ->prefix((string) config('platform.prefix', 'admin'))
    ->group(function (): void {
        Route::screen('main', DashboardScreen::class)
            ->name('platform.main')
            ->breadcrumbs(fn (Trail $trail) => $trail->push(__('Панель управления'), route('platform.main')));

        Route::screen('profile', UserProfileScreen::class)
            ->name('platform.profile')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Профиль'), route('platform.profile')));

        Route::screen('users/{user}/edit', UserEditScreen::class)
            ->name('platform.systems.users.edit')
            ->breadcrumbs(fn (Trail $trail, $user) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Пользователи'), route('platform.systems.users'))
                ->push(__('Редактирование пользователя'), route('platform.systems.users.edit', $user)));

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

        Route::screen('roles', RoleListScreen::class)
            ->name('platform.systems.roles')
            ->breadcrumbs(fn (Trail $trail) => $trail
                ->push(__('Панель управления'), route('platform.main'))
                ->push(__('Роли'), route('platform.systems.roles')));
    });
