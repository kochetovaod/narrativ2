<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// Главная страница
Route::get('/', [PublicController::class, 'home'])->name('home');

// Категории продукции
Route::get('/products', [PublicController::class, 'products'])->name('products.index');
Route::get('/products/{categorySlug}', [PublicController::class, 'productsByCategory'])
    ->name('products.category');

// Товары
Route::get('/products/{categorySlug}/{productSlug}', [PublicController::class, 'product'])
    ->name('products.show');

// Услуги
Route::get('/services', [PublicController::class, 'services'])->name('services.index');
Route::get('/services/{serviceSlug}', [PublicController::class, 'service'])
    ->name('services.show');

// Портфолио
Route::get('/portfolio', [PublicController::class, 'portfolio'])->name('portfolio.index');
Route::get('/portfolio/{caseSlug}', [PublicController::class, 'portfolioCase'])
    ->name('portfolio.show');

// Новости
Route::get('/news', [PublicController::class, 'news'])->name('news.index');
Route::get('/news/{newsSlug}', [PublicController::class, 'newsPost'])
    ->name('news.show');

// Статические страницы
Route::get('/{pageSlug}', [PublicController::class, 'page'])
    ->name('pages.show');

// Поиск
Route::get('/search', [PublicController::class, 'search'])->name('search');

// Предпросмотр страниц
Route::get('/preview/page/{token}', [PreviewController::class, 'page'])
    ->name('preview.page');

// Предпросмотр глобальных блоков
Route::get('/preview/block/{code}', [PreviewController::class, 'globalBlock'])
    ->name('preview.global_block');

// Формы
Route::post('/forms/submit/{formCode}', [FormController::class, 'submit'])
    ->name('forms.submit');

Route::get('/forms/preview/{formCode}', [FormController::class, 'preview'])
    ->name('forms.preview');

Route::get('/health', fn () => ['status' => 'ok']);
