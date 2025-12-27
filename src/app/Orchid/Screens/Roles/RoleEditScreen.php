<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Roles;

use App\Orchid\Permissions\Rbac;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBoxList;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class RoleEditScreen extends Screen
{
    public Role $role;

    /**
     * @var string|array<int, string>
     */
    public $permission = Rbac::PERMISSION_ROLES;

    public function query(Role $role): iterable
    {
        $role->load('users');
        $role->permissions = array_keys(array_filter($role->permissions ?? []));

        return [
            'role' => $role,
            'permissionOptions' => $this->permissionOptions(),
            'isProtected' => in_array($role->slug, Rbac::protectedRoles(), true),
        ];
    }

    public function name(): ?string
    {
        return $this->role->exists
            ? __('Редактирование роли')
            : __('Создание роли');
    }

    public function description(): ?string
    {
        return __('Настройка ролей и прав доступа');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить эту роль? Пользователи потеряют доступ, если не будут добавлены в другие роли.'))
                ->method('remove')
                ->canSee($this->role->exists && ! in_array($this->role->slug, Rbac::protectedRoles(), true)),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.roles'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('role.name')
                    ->title(__('Название'))
                    ->required(),

                Input::make('role.slug')
                    ->title(__('Ключ (slug)'))
                    ->help(__('Используется в коде для привязки к enum роли'))
                    ->required()
                    ->disabled(in_array($this->role->slug, Rbac::protectedRoles(), true)),

                CheckBoxList::make('role.permissions')
                    ->title(__('Права'))
                    ->options($this->permissionOptions()),
            ])->title(__('Основные настройки')),
        ];
    }

    public function save(Role $role, Request $request): void
    {
        $data = $request->validate([
            'role.name' => ['required', 'string', 'max:255'],
            'role.slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::in(Rbac::protectedRoles()),
                Rule::unique(Role::class, 'slug')->ignore($role),
            ],
            'role.permissions' => ['array'],
            'role.permissions.*' => ['string'],
        ])['role'];

        $role->fill([
            'name' => $data['name'],
            'slug' => $role->exists ? $role->slug : $data['slug'],
            'permissions' => $this->mapPermissions($data['permissions'] ?? []),
        ]);

        $role->save();

        Alert::info(__('Роль сохранена'));

        $this->redirect(route('platform.systems.roles'));
    }

    public function remove(Role $role): void
    {
        if (in_array($role->slug, Rbac::protectedRoles(), true)) {
            Alert::warning(__('Системные роли нельзя удалять'));

            return;
        }

        $role->delete();

        Alert::info(__('Роль удалена'));

        $this->redirect(route('platform.systems.roles'));
    }

    /**
     * @return array<string, string>
     */
    protected function permissionOptions(): array
    {
        return collect(Rbac::permissionGroups())
            ->flatMap(function (array $permissions, string $group): array {
                return collect($permissions)
                    ->mapWithKeys(fn (string $label, string $key) => [$key => "{$group}: {$label}"])
                    ->all();
            })
            ->all();
    }

    /**
     * @param  array<int, string>  $permissions
     * @return array<string, bool>
     */
    protected function mapPermissions(array $permissions): array
    {
        return collect($permissions)
            ->mapWithKeys(fn (string $permission): array => [$permission => true])
            ->all();
    }
}
