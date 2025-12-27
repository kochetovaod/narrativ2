<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Users;

use App\Models\User;
use App\Orchid\Permissions\Rbac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBoxList;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class UserEditScreen extends Screen
{
    public User $user;

    /**
     * @var string|array<int, string>
     */
    public $permission = Rbac::PERMISSION_USERS;

    /**
     * @var array<int|string, string>
     */
    protected array $roleOptions = [];

    public function query(User $user): iterable
    {
        $user->load('roles');
        $user->role_id = $user->roles->first()?->id;

        $this->roleOptions = Role::query()
            ->whereIn('slug', Rbac::protectedRoles())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        return [
            'user' => $user,
            'roles' => $this->roleOptions,
            'permissions' => array_keys(array_filter($user->permissions ?? [])),
            'permissionOptions' => $this->permissionOptions(),
        ];
    }

    public function name(): ?string
    {
        return $this->user->exists ? __('Редактирование пользователя') : __('Создание пользователя');
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(__('Сохранить'))
                ->icon('check')
                ->method('save'),

            Button::make(__('Удалить'))
                ->icon('trash')
                ->confirm(__('Удалить этого пользователя? Доступ в панель будет закрыт.'))
                ->method('remove')
                ->canSee($this->user->exists),

            Link::make(__('Назад'))
                ->icon('action-undo')
                ->route('platform.systems.users'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('user.name')
                    ->title(__('Имя'))
                    ->placeholder(__('ФИО'))
                    ->required(),

                Input::make('user.email')
                    ->title('Email')
                    ->type('email')
                    ->required(),

                Password::make('user.password')
                    ->title(__('Пароль'))
                    ->placeholder(__('Оставьте пустым, чтобы не менять'))
                    ->required(! $this->user->exists)
                    ->min(8),

                Select::make('user.role_id')
                    ->title(__('Роль'))
                    ->options($this->roleOptions)
                    ->value($this->user->role_id)
                    ->required(),

                Switcher::make('user.is_active')
                    ->title(__('Активен'))
                    ->sendTrueOrFalse()
                    ->help(__('Неактивные пользователи не могут войти в панель')),

                CheckBoxList::make('permissions')
                    ->title(__('Дополнительные права'))
                    ->options($this->permissionOptions())
                    ->placeholder(__('Права задаются ролью. Здесь можно включить точечно.'))
                    ->columns(2),
            ])->title(__('Общие данные')),
        ];
    }

    public function save(User $user, Request $request): void
    {
        $payload = $request->validate([
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email')->ignore($user),
            ],
            'user.password' => [$user->exists ? 'nullable' : 'required', 'string', 'min:8'],
            'user.role_id' => ['required', 'exists:roles,id'],
            'user.is_active' => ['nullable', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $userData = $payload['user'];
        $permissions = $this->mapPermissions($payload['permissions'] ?? []);

        $user->fill([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'is_active' => $userData['is_active'] ?? false,
        ]);

        if (! empty($userData['password'])) {
            $user->password = Hash::make($userData['password']);
        }

        $role = Role::find($userData['role_id']);
        if ($role !== null) {
            $user->role = $role->slug;
        }

        $user->permissions = $permissions;

        $user->save();

        if ($role !== null) {
            $user->roles()->sync([$role->id]);
        }

        Alert::info(__('Пользователь сохранен'));

        $this->redirect(route('platform.systems.users'));
    }

    public function remove(User $user): void
    {
        $user->delete();

        Alert::info(__('Пользователь удален'));

        $this->redirect(route('platform.systems.users'));
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
