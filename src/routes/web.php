<?php

use App\Models\Page;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Предпросмотр страниц
Route::get('/preview/{token}', function ($token) {
    $page = Page::where('preview_token', $token)->first();
    
    if (!$page) {
        abort(404, 'Страница не найдена или токен просрочен');
    }
    
    return view('preview.page', compact('page'));
})->name('preview.page');

Route::get('/health', fn () => ['status' => 'ok']);
