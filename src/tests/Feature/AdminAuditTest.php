<?php

namespace Tests\Feature;

use App\Models\AdminAudit;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_actions_are_logged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $page = Page::factory()->create(['title' => 'Страница', 'slug' => 'page-1']);

        $page->update(['title' => 'Страница 2']);
        $page->delete();

        $this->assertEquals(3, AdminAudit::query()->count());

        $this->assertDatabaseHas('admin_audits', [
            'user_id' => $user->id,
            'auditable_type' => Page::class,
            'auditable_id' => $page->id,
            'action' => 'updated',
        ]);
    }
}
