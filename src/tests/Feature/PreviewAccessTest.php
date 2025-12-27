<?php

namespace Tests\Feature;

use App\Models\GlobalBlock;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_page_preview_available_with_token(): void
    {
        $page = Page::factory()->create([
            'status' => 'draft',
            'published_at' => null,
            'slug' => 'preview-page',
        ]);

        $this->get(route('preview.page', ['token' => $page->preview_token]))
            ->assertOk()
            ->assertSee($page->title);

        $this->get(route('preview.page', ['token' => 'wrong-token']))
            ->assertNotFound();
    }

    public function test_global_block_preview_respects_active_flag(): void
    {
        $block = GlobalBlock::factory()->create([
            'is_active' => true,
            'code' => 'cta-block',
        ]);

        $this->get(route('preview.global_block', ['code' => $block->code]))
            ->assertOk()
            ->assertSee($block->title);

        $inactive = GlobalBlock::factory()->create([
            'is_active' => false,
            'code' => 'inactive-block',
        ]);

        $this->get(route('preview.global_block', ['code' => $inactive->code]))
            ->assertNotFound();
    }
}
