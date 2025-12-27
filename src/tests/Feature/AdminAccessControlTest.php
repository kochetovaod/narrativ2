<?php

namespace Tests\Feature;

use App\Models\User;
use App\Orchid\Permissions\Rbac;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('platform.main'))
            ->assertRedirect(route('platform.login'));
    }

    public function test_user_without_permission_gets_forbidden(): void
    {
        $user = User::factory()->create([
            'permissions' => [
                Rbac::PERMISSION_ACCESS => true,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('platform.leads.index'))
            ->assertStatus(403);
    }

    public function test_user_with_permission_can_open_lead_screen(): void
    {
        $user = User::factory()->create([
            'permissions' => [
                Rbac::PERMISSION_ACCESS => true,
                'platform.leads' => true,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('platform.leads.index'))
            ->assertOk();
    }
}
