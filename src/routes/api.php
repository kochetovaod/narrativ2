<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('api')
    ->group(function (): void {
        Route::get('/health', fn () => ['status' => 'ok']);
    });
