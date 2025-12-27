<?php

namespace Tests\Unit;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_token_generated_and_published_at_synced(): void
    {
        $page = Page::factory()->create(['status' => 'draft', 'published_at' => null, 'preview_token' => null]);

        $this->assertNotNull($page->preview_token);
        $this->assertNull($page->published_at);

        $page->publish();
        $page->save();

        $this->assertEquals('published', $page->status);
        $this->assertNotNull($page->published_at);

        $page->setDraft();
        $page->save();

        $this->assertEquals('draft', $page->status);
        $this->assertNull($page->published_at);
    }

    public function test_preview_access_controlled_by_token(): void
    {
        $page = Page::factory()->create(['status' => 'draft']);

        $this->assertTrue($page->canBePreviewed($page->preview_token));
        $this->assertFalse($page->canBePreviewed('wrong-token'));
    }
}
