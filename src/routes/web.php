<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');

// Главная страница
Route::get('/', [PublicController::class, 'home'])->name('home');

// Категории продукции
Route::prefix('/produkciya')->group(function () {
    Route::get('/', [PublicController::class, 'products'])->name('products.index');
    Route::get('/{categorySlug}', [PublicController::class, 'productsByCategory'])
        ->name('products.category');

    // Товары
    Route::get('/{categorySlug}/{productSlug}', [PublicController::class, 'product'])
        ->name('products.show');
});

// Услуги
Route::prefix('/uslugi')->group(function () {
    Route::get('/', [PublicController::class, 'services'])->name('services.index');
    Route::get('/{serviceSlug}', [PublicController::class, 'service'])
        ->name('services.show');
});

// Портфолио
Route::get('/portfolio', [PublicController::class, 'portfolio'])->name('portfolio.index');
Route::get('/portfolio/{caseSlug}', [PublicController::class, 'portfolioCase'])
    ->name('portfolio.show');

// Новости
Route::get('/news', [PublicController::class, 'news'])->name('news.index');
Route::get('/news/{newsSlug}', [PublicController::class, 'newsPost'])
    ->name('news.show');

// Предпросмотр страниц
Route::get('/preview/page/{token}', [PreviewController::class, 'page'])
    ->name('preview.page');

// Предпросмотр глобальных блоков
Route::get('/preview/block/{code}', [PreviewController::class, 'globalBlock'])
    ->name('preview.global_block');

// Поиск
Route::get('/search/suggestions', [PublicController::class, 'searchSuggestions'])
    ->name('search.suggestions');
Route::get('/search', [PublicController::class, 'search'])->name('search');

// Формы
Route::post('/forms/submit/{formCode}', [FormController::class, 'submit'])
    ->name('forms.submit');

Route::get('/forms/preview/{formCode}', [FormController::class, 'preview'])
    ->name('forms.preview');

Route::get('/health', fn () => ['status' => 'ok']);

// Контакты
Route::get('/kontakty', [PublicController::class, 'contacts'])->name('contacts');

// Документы
Route::get('/privacy', [PublicController::class, 'document'])
    ->defaults('documentCode', 'privacy')
    ->name('documents.privacy');
Route::get('/consent', [PublicController::class, 'document'])
    ->defaults('documentCode', 'consent')
    ->name('documents.consent');
Route::get('/terms', [PublicController::class, 'document'])
    ->defaults('documentCode', 'terms')
    ->name('documents.terms');
Route::get('/cookies', [PublicController::class, 'document'])
    ->defaults('documentCode', 'cookies')
    ->name('documents.cookies');

// Статические страницы
Route::get('/{pageSlug}', [PublicController::class, 'page'])
    ->name('pages.show');
