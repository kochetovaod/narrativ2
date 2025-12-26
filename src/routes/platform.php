<?php

declare(strict_types=1);

use App\Orchid\Screens\DashboardScreen;
use Illuminate\Support\Facades\Route;
use Orchid\Platform\Screens\Role\RoleEditScreen;
use Orchid\Platform\Screens\Role\RoleListScreen;
use Orchid\Platform\Screens\User\UserEditScreen;
use Orchid\Platform\Screens\User\UserListScreen;
use Orchid\Platform\Screens\User\UserProfileScreen;

Route::middleware((array) config('platform.middleware.private'))
    ->domain(config('platform.domain'))
    ->prefix((string) config('platform.prefix', 'admin'))
    ->group(function () {
        Route::screen('main', DashboardScreen::class)
            ->name('platform.main');

        Route::screen('profile', UserProfileScreen::class)
            ->name('platform.profile');

        Route::screen('users/{user}/edit', UserEditScreen::class)
            ->name('platform.systems.users.edit');

        Route::screen('users', UserListScreen::class)
            ->name('platform.systems.users');

        Route::screen('roles/{role}/edit', RoleEditScreen::class)
            ->name('platform.systems.roles.edit');

        Route::screen('roles', RoleListScreen::class)
            ->name('platform.systems.roles');
    });
