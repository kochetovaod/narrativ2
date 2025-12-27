<?php

namespace Database\Seeders;

use App\Models\User;
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
        $adminEmail = env('ORCHID_ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ORCHID_ADMIN_PASSWORD', 'password');
        $adminName = env('ORCHID_ADMIN_NAME', 'Administrator');

        $role = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Администратор',
                'permissions' => [
                    'platform.index' => true,
                    'platform.systems.roles' => true,
                    'platform.systems.users' => true,
                ],
            ],
        );

        $user = User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'permissions' => $role->permissions,
                'role' => 'super_admin',
                'is_active' => true,
            ],
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
