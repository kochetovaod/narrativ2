<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Users;

use App\Models\User;
use App\Orchid\Permissions\Rbac;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class UserListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = Rbac::PERMISSION_USERS;

    public function query(): iterable
    {
        return [
            'users' => User::with('roles')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    public function name(): ?string
    {
        return __('Пользователи');
    }

    public function description(): ?string
    {
        return __('Управление пользователями и их ролями');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Создать пользователя'))
                ->icon('plus')
                ->route('platform.systems.users.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('users', [
                TD::make('name', __('Имя'))
                    ->render(fn (User $user) => Link::make($user->name ?? $user->email)->route('platform.systems.users.edit', $user)),
                TD::make('email', __('Email'))->sort(),
                TD::make('role', __('Роль'))
                    ->render(fn (User $user) => $user->roles->first()->name ?? '—')
                    ->filter(
                        TD::FILTER_SELECT,
                        Role::query()
                            ->whereIn('slug', Rbac::protectedRoles())
                            ->pluck('name', 'slug')
                            ->all()
                    ),
                TD::make('is_active', __('Активен'))
                    ->render(fn (User $user) => $user->is_active ? __('Да') : __('Нет'))
                    ->sort(),
                TD::make('updated_at', __('Обновлено'))
                    ->render(fn (User $user) => $user->updated_at?->toDateTimeString())
                    ->sort(),
            ]),
        ];
    }
}
