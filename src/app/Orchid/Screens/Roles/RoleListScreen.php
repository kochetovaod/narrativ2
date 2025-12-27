<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Roles;

use App\Orchid\Permissions\Rbac;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class RoleListScreen extends Screen
{
    /**
     * @var string|array<int, string>
     */
    public $permission = Rbac::PERMISSION_ROLES;

    public function query(): iterable
    {
        return [
            'roles' => Role::defaultSort('name')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return __('Роли');
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Создать роль'))
                ->icon('plus')
                ->route('platform.systems.roles.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('roles', [
                TD::make('name', __('Название'))
                    ->render(fn (Role $role) => Link::make($role->name)->route('platform.systems.roles.edit', $role)),
                TD::make('slug', __('Ключ'))->sort(),
                TD::make('created_at', __('Создано'))->render(fn (Role $role) => $role->created_at?->toDateTimeString())->sort(),
                TD::make('updated_at', __('Обновлено'))->render(fn (Role $role) => $role->updated_at?->toDateTimeString())->sort(),
            ]),
        ];
    }
}
