<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

class PreviewController extends Controller
{
    /**
     * Показать предпросмотр страницы по токену
     */
    public function page(string $token): Response
    {
        $page = Page::where('preview_token', $token)
            ->where('status', 'draft')
            ->first();

        if (! $page) {
            abort(404, __('Страница не найдена или недоступна для предпросмотра'));
        }

        // Генерируем meta информацию
        $seoTitle = $page->seo['title'] ?? $page->title;
        $seoDescription = $page->seo['description'] ?? '';
        $seoH1 = $page->seo['h1'] ?? $page->title;

        return response()->view('preview.page', [
            'page' => $page,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'seoH1' => $seoH1,
            'isPreview' => true,
        ]);
    }

    /**
     * Показать предпросмотр глобального блока
     */
    public function globalBlock(string $code): Response
    {
        $block = \App\Models\GlobalBlock::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $block) {
            abort(404, __('Глобальный блок не найден'));
        }

        return response()->view('preview.global_block', [
            'block' => $block,
            'isPreview' => true,
        ]);
    }
}
