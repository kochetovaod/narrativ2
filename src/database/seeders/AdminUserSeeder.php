<?php

namespace Database\Seeders;

use App\Models\User;
use App\Orchid\Permissions\Rbac;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Orchid\Platform\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect(Rbac::rolePresets())->map(function (array $roleData, string $slug): Role {
            return Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'permissions' => $roleData['permissions'],
                ],
            );
        });

        $users = [
            Rbac::ROLE_SUPER_ADMIN => [
                'email' => env('ORCHID_ADMIN_EMAIL', 'admin@example.com'),
                'password' => env('ORCHID_ADMIN_PASSWORD', 'password'),
                'name' => env('ORCHID_ADMIN_NAME', 'Administrator'),
            ],
            Rbac::ROLE_ADMIN => [
                'email' => env('ORCHID_MANAGER_EMAIL', 'manager@example.com'),
                'password' => env('ORCHID_MANAGER_PASSWORD', 'password'),
                'name' => env('ORCHID_MANAGER_NAME', 'Manager'),
            ],
            Rbac::ROLE_CONTENT_MANAGER => [
                'email' => env('ORCHID_CONTENT_EMAIL', 'content@example.com'),
                'password' => env('ORCHID_CONTENT_PASSWORD', 'password'),
                'name' => env('ORCHID_CONTENT_NAME', 'Content Manager'),
            ],
        ];

        foreach ($users as $roleSlug => $userData) {
            /** @var Role|null $role */
            $role = $roles->get($roleSlug);

            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'permissions' => $role?->permissions ?? [],
                    'role' => $roleSlug,
                    'is_active' => true,
                ],
            );

            if ($role !== null) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }
}
