<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Support\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformUserIsActive
{
    /**
     * Проверяет, что пользователь активен перед доступом в панель управления.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Ваш аккаунт деактивирован. Обратитесь к администратору.'),
                ], Response::HTTP_FORBIDDEN);
            }

            Alert::warning(__('Ваш аккаунт деактивирован. Обратитесь к администратору.'));

            return redirect()->route('platform.login');
        }

        return $next($request);
    }
}
